<?php

declare(strict_types=1);

$inputPath = __DIR__ . '/data/hotel_images_by_name.json';
$outputPath = __DIR__ . '/data/hotel_images_all_source_to_link.sql';

function escSql(string $value): string
{
    $value = str_replace(["\r", "\n"], ' ', $value);
    return str_replace("'", "''", $value);
}

$json = file_get_contents($inputPath);
if ($json === false) {
    fwrite(STDERR, "Unable to read JSON: {$inputPath}\n");
    exit(1);
}

$data = json_decode($json, true);
if (!is_array($data)) {
    fwrite(STDERR, "Invalid JSON in {$inputPath}\n");
    exit(1);
}

$rows = [];
$total = 0;

foreach ($data as $rawKey => $urls) {
    if (!is_array($urls)) {
        continue;
    }

    $cleaned = preg_replace('/\s*[0-9]+\s*Star Hotel.*$/i', '', (string)$rawKey) ?? (string)$rawKey;
    $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned) ?? $cleaned);

    foreach (array_values($urls) as $index => $url) {
        $position = (int)$index + 1;
        $rows[] = sprintf(
            "('%s','%s','%s',%d)",
            escSql((string)$rawKey),
            escSql($cleaned),
            escSql((string)$url),
            $position
        );
        $total++;
    }
}

$sql = [];
$sql[] = '-- Generated from data/hotel_images_by_name.json';
$sql[] = '-- Source rows: ' . $total;
$sql[] = 'SET NAMES utf8mb4;';
$sql[] = '';
$sql[] = 'DROP TEMPORARY TABLE IF EXISTS tmp_hotel_image_source;';
$sql[] = 'CREATE TEMPORARY TABLE tmp_hotel_image_source (';
$sql[] = '  id INT UNSIGNED NOT NULL AUTO_INCREMENT,';
$sql[] = '  raw_key TEXT NOT NULL,';
$sql[] = '  cleaned_name VARCHAR(255) NOT NULL,';
$sql[] = '  image_url VARCHAR(1000) NOT NULL,';
$sql[] = '  url_position INT UNSIGNED NOT NULL,';
$sql[] = '  PRIMARY KEY (id),';
$sql[] = '  KEY idx_cleaned_name (cleaned_name)';
$sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;';
$sql[] = '';
$sql[] = 'INSERT INTO tmp_hotel_image_source (raw_key, cleaned_name, image_url, url_position) VALUES';
$sql[] = implode(",\n", $rows) . ';';
$sql[] = '';
$sql[] = 'INSERT INTO link_hotel_images (hotel_id, image_position, url_position, caption, image_url, raw_json)';
$sql[] = 'WITH matched AS (';
$sql[] = '  SELECT';
$sql[] = '    s.id AS src_id,';
$sql[] = '    s.raw_key,';
$sql[] = '    s.cleaned_name,';
$sql[] = '    s.image_url,';
$sql[] = '    s.url_position,';
$sql[] = '    h.id AS hotel_id,';
$sql[] = '    ROW_NUMBER() OVER (';
$sql[] = '      PARTITION BY s.id';
$sql[] = '      ORDER BY';
$sql[] = "        CASE";
$sql[] = "          WHEN REGEXP_REPLACE(LOWER(TRIM(s.cleaned_name)), '[^a-z0-9]+', '') = REGEXP_REPLACE(LOWER(TRIM(h.name)), '[^a-z0-9]+', '') THEN 0";
$sql[] = "          WHEN REGEXP_REPLACE(LOWER(TRIM(s.cleaned_name)), '[^a-z0-9]+', '') LIKE CONCAT('%', REGEXP_REPLACE(LOWER(TRIM(h.name)), '[^a-z0-9]+', ''), '%') THEN 1";
$sql[] = '          ELSE 2';
$sql[] = '        END,';
$sql[] = '        CHAR_LENGTH(TRIM(h.name)) DESC,';
$sql[] = '        h.id ASC';
$sql[] = '    ) AS rn';
$sql[] = '  FROM tmp_hotel_image_source s';
$sql[] = '  JOIN link_hotels h';
$sql[] = '    ON (';
$sql[] = "      REGEXP_REPLACE(LOWER(TRIM(s.cleaned_name)), '[^a-z0-9]+', '') = REGEXP_REPLACE(LOWER(TRIM(h.name)), '[^a-z0-9]+', '')";
$sql[] = "      OR REGEXP_REPLACE(LOWER(TRIM(s.cleaned_name)), '[^a-z0-9]+', '') LIKE CONCAT('%', REGEXP_REPLACE(LOWER(TRIM(h.name)), '[^a-z0-9]+', ''), '%')";
$sql[] = '    )';
$sql[] = ')';
$sql[] = 'SELECT';
$sql[] = '  m.hotel_id,';
$sql[] = '  m.url_position AS image_position,';
$sql[] = '  m.url_position,';
$sql[] = "  CONCAT('Room ', m.url_position) AS caption,";
$sql[] = '  m.image_url,';
$sql[] = '  JSON_OBJECT(';
$sql[] = "    'source_key', m.raw_key,";
$sql[] = "    'parsed_name', m.cleaned_name,";
$sql[] = "    'caption', CONCAT('Room ', m.url_position),";
$sql[] = "    'image_url', m.image_url";
$sql[] = '  ) AS raw_json';
$sql[] = 'FROM matched m';
$sql[] = 'WHERE m.rn = 1';
$sql[] = '  AND NOT EXISTS (';
$sql[] = '    SELECT 1';
$sql[] = '    FROM link_hotel_images lhi';
$sql[] = '    WHERE lhi.hotel_id = m.hotel_id';
$sql[] = '      AND lhi.image_url = m.image_url';
$sql[] = '  );';
$sql[] = '';
$sql[] = '-- Diagnostic counts';
$sql[] = 'SELECT COUNT(*) AS source_rows FROM tmp_hotel_image_source;';
$sql[] = 'SELECT COUNT(*) AS unmatched_rows';
$sql[] = 'FROM tmp_hotel_image_source s';
$sql[] = 'LEFT JOIN link_hotels h';
$sql[] = '  ON (';
$sql[] = "    REGEXP_REPLACE(LOWER(TRIM(s.cleaned_name)), '[^a-z0-9]+', '') = REGEXP_REPLACE(LOWER(TRIM(h.name)), '[^a-z0-9]+', '')";
$sql[] = "    OR REGEXP_REPLACE(LOWER(TRIM(s.cleaned_name)), '[^a-z0-9]+', '') LIKE CONCAT('%', REGEXP_REPLACE(LOWER(TRIM(h.name)), '[^a-z0-9]+', ''), '%')";
$sql[] = '  )';
$sql[] = 'WHERE h.id IS NULL;';

$result = file_put_contents($outputPath, implode("\n", $sql) . "\n");
if ($result === false) {
    fwrite(STDERR, "Failed to write SQL file: {$outputPath}\n");
    exit(1);
}

echo "Created {$outputPath} with {$total} source rows\n";
