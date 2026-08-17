<?php

declare(strict_types=1);

$jsonPath = __DIR__ . '/data/hotel_images_by_name.json';
$sqlitePath = __DIR__ . '/data/new.sqlite.db';
$outPath = __DIR__ . '/data/hotel_images_insert_all.sql';
$unmatchedPath = __DIR__ . '/data/hotel_images_unmatched.csv';

function normalizeName(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;
    return preg_replace('/[^a-z0-9]+/', '', $value) ?? $value;
}

function sqlEscape(string $value): string
{
    $value = str_replace(["\r", "\n"], ' ', $value);
    return str_replace("'", "''", $value);
}

$raw = file_get_contents($jsonPath);
if ($raw === false) {
    fwrite(STDERR, "Unable to read JSON: {$jsonPath}\n");
    exit(1);
}

$data = json_decode($raw, true);
if (!is_array($data)) {
    fwrite(STDERR, "Invalid JSON: {$jsonPath}\n");
    exit(1);
}

$db = new SQLite3($sqlitePath, SQLITE3_OPEN_READONLY);
$res = $db->query('SELECT id, name FROM link_hotels');
if ($res === false) {
    fwrite(STDERR, "Failed to query link_hotels from sqlite snapshot\n");
    exit(1);
}

$hotels = [];
$hotelsByExternalId = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $name = (string)$row['name'];
    $hotels[] = [
        'id' => (int)$row['id'],
        'name' => $name,
        'norm' => normalizeName($name),
        'len' => strlen(trim($name)),
    ];
}

$resExt = $db->query('SELECT id, external_hotel_id FROM link_hotels');
if ($resExt !== false) {
    while ($row = $resExt->fetchArray(SQLITE3_ASSOC)) {
        $ext = trim((string)($row['external_hotel_id'] ?? ''));
        if ($ext !== '') {
            $hotelsByExternalId[$ext] = (int)$row['id'];
        }
    }
}

$rows = [];
$unmatched = [];

foreach ($data as $rawKey => $urls) {
    if (!is_array($urls)) {
        continue;
    }

    $parsedName = preg_replace('/\s*[0-9]+\s*Star Hotel.*$/i', '', (string)$rawKey) ?? (string)$rawKey;
    $parsedName = trim(preg_replace('/\s+/', ' ', $parsedName) ?? $parsedName);
    $parsedNorm = normalizeName($parsedName);

    $candidates = [];
    foreach ($hotels as $hotel) {
        if ($parsedNorm === $hotel['norm']) {
            $rank = 0;
        } elseif (strpos($parsedNorm, $hotel['norm']) !== false) {
            $rank = 1;
        } else {
            continue;
        }

        $candidates[] = [
            'rank' => $rank,
            'len' => $hotel['len'],
            'id' => $hotel['id'],
            'hotel' => $hotel,
        ];
    }

    if ($candidates === []) {
        // Fallback: extract external_hotel_id from image URL pattern .../cms/{group}/{hotelId}/images/...
        $fallbackHotelId = null;
        foreach (array_values($urls) as $url) {
            if (preg_match('~/cms/\d+/(\d+)/images/~', (string)$url, $m)) {
                $extId = $m[1];
                if (isset($hotelsByExternalId[$extId])) {
                    $fallbackHotelId = $hotelsByExternalId[$extId];
                    break;
                }
            }
        }

        if ($fallbackHotelId !== null) {
            $hotel = [
                'id' => $fallbackHotelId,
                'name' => $parsedName,
                'norm' => $parsedNorm,
                'len' => strlen($parsedName),
            ];

            foreach (array_values($urls) as $i => $url) {
                $position = (int)$i + 1;
                $caption = 'Room ' . $position;
                $imageUrl = (string)$url;
                $rawJson = json_encode(
                    [
                        'source_key' => $rawKey,
                        'parsed_name' => $parsedName,
                        'caption' => $caption,
                        'image_url' => $imageUrl,
                    ],
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                $rows[] = sprintf(
                    "(%d, %d, %d, '%s', '%s', '%s')",
                    (int)$hotel['id'],
                    $position,
                    $position,
                    sqlEscape($caption),
                    sqlEscape($imageUrl),
                    sqlEscape((string)$rawJson)
                );
            }
            continue;
        }

        foreach (array_values($urls) as $i => $url) {
            $unmatched[] = [$rawKey, $parsedName, (int)$i + 1, (string)$url];
        }
        continue;
    }

    usort(
        $candidates,
        static function (array $a, array $b): int {
            if ($a['rank'] !== $b['rank']) {
                return $a['rank'] <=> $b['rank'];
            }
            if ($a['len'] !== $b['len']) {
                return $b['len'] <=> $a['len'];
            }
            return $a['id'] <=> $b['id'];
        }
    );

    $hotel = $candidates[0]['hotel'];

    foreach (array_values($urls) as $i => $url) {
        $position = (int)$i + 1;
        $caption = 'Room ' . $position;
        $imageUrl = (string)$url;
        $rawJson = json_encode(
            [
                'source_key' => $rawKey,
                'parsed_name' => $parsedName,
                'caption' => $caption,
                'image_url' => $imageUrl,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $rows[] = sprintf(
            "(%d, %d, %d, '%s', '%s', '%s')",
            (int)$hotel['id'],
            $position,
            $position,
            sqlEscape($caption),
            sqlEscape($imageUrl),
            sqlEscape((string)$rawJson)
        );
    }
}

if ($rows === []) {
    fwrite(STDERR, "No matched rows generated.\n");
    exit(1);
}

$sql = "-- Generated from data/hotel_images_by_name.json using link_hotels names from data/new.sqlite.db\n";
$sql .= '-- Total rows: ' . count($rows) . "\n";
$sql .= '-- Unmatched rows: ' . count($unmatched) . "\n\n";
$sql .= "SET NAMES utf8mb4;\n\n";
$sql .= "INSERT INTO link_hotel_images (hotel_id, image_position, url_position, caption, image_url, raw_json) VALUES\n";
$sql .= implode(",\n", $rows);
$sql .= "\n;\n";

if (file_put_contents($outPath, $sql) === false) {
    fwrite(STDERR, "Failed to write SQL: {$outPath}\n");
    exit(1);
}

$uf = fopen($unmatchedPath, 'w');
if ($uf === false) {
    fwrite(STDERR, "Failed to write unmatched CSV: {$unmatchedPath}\n");
    exit(1);
}

fputcsv($uf, ['raw_key', 'parsed_name', 'url_position', 'image_url'], ',', '"', '\\');
foreach ($unmatched as $entry) {
    fputcsv($uf, $entry, ',', '"', '\\');
}
fclose($uf);

echo "SQL file: {$outPath}\n";
echo "Unmatched CSV: {$unmatchedPath}\n";
echo 'Rows generated: ' . count($rows) . "\n";
echo 'Rows unmatched: ' . count($unmatched) . "\n";
