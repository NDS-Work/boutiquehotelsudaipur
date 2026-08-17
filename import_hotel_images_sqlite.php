<?php

declare(strict_types=1);

$jsonPath = __DIR__ . '/data/hotel_images_by_name.json';
$dbPath = __DIR__ . '/data/new.sqlite.db';
$unmatchedPath = __DIR__ . '/data/hotel_images_unmatched_after_sqlite_import.csv';

function normalizeName(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;
    return preg_replace('/[^a-z0-9]+/', '', $value) ?? $value;
}

$raw = file_get_contents($jsonPath);
if ($raw === false) {
    fwrite(STDERR, "Unable to read JSON file: {$jsonPath}\n");
    exit(1);
}

$data = json_decode($raw, true);
if (!is_array($data)) {
    fwrite(STDERR, "Invalid JSON in {$jsonPath}\n");
    exit(1);
}

$db = new SQLite3($dbPath);
$db->exec('PRAGMA foreign_keys = ON');

$hotelQuery = $db->query('SELECT id, name, external_hotel_id FROM link_hotels');
if ($hotelQuery === false) {
    fwrite(STDERR, "Failed to load hotels from SQLite DB\n");
    exit(1);
}

$hotels = [];
$hotelsByExternalId = [];
while ($row = $hotelQuery->fetchArray(SQLITE3_ASSOC)) {
    $name = (string)($row['name'] ?? '');
    $externalId = trim((string)($row['external_hotel_id'] ?? ''));

    $hotel = [
        'id' => (int)$row['id'],
        'name' => $name,
        'norm' => normalizeName($name),
        'len' => strlen(trim($name)),
    ];

$totalImages = 0;
$inserted = 0;
$skippedExisting = 0;
$matchedByName = 0;
$matchedByExternal = 0;
$unmatched = [];

$transactionActive = false;
try {
    $db->exec('BEGIN');
    $transactionActive = true;

    foreach ($data as $rawKey => $urls) {
        if (!is_array($urls)) {
            continue;
        }

        $parsedName = preg_replace('/\s*[0-9]+\s*Star Hotel.*$/i', '', (string)$rawKey) ?? (string)$rawKey;
        $parsedName = trim(preg_replace('/\s+/', ' ', $parsedName) ?? $parsedName);
        $parsedNorm = normalizeName($parsedName);

        $candidateRows = [];
        foreach ($hotels as $hotel) {
            if ($parsedNorm === $hotel['norm']) {
                $rank = 0;
            } elseif (strpos($parsedNorm, $hotel['norm']) !== false || strpos($hotel['norm'], $parsedNorm) !== false) {
                $rank = 1;
            } else {
                continue;
            }

            $candidateRows[] = [
                'rank' => $rank,
                'len' => $hotel['len'],
                'id' => $hotel['id'],
            ];
        }

        $hotelId = null;
        if ($candidateRows !== []) {
            usort(
                $candidateRows,
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
            $hotelId = $candidateRows[0]['id'];
            $matchedByName++;
        } else {
            foreach (array_values($urls) as $url) {
                if (preg_match('~/cms/\d+/(\d+)/images/~', (string)$url, $m)) {
                    $externalId = $m[1];
                    if (isset($hotelsByExternalId[$externalId])) {
                        $hotelId = $hotelsByExternalId[$externalId];
                        $matchedByExternal++;
                        break;
                    }
                }
            }
        }

        foreach (array_values($urls) as $i => $url) {
            $totalImages++;
            $position = (int)$i + 1;
            $imageUrl = (string)$url;

            if ($hotelId === null) {
                $unmatched[] = [$rawKey, $parsedName, $position, $imageUrl];
                continue;
            }

            $checkStmt->reset();
            $checkStmt->clear();
            $checkStmt->bindValue(':hotel_id', $hotelId, SQLITE3_INTEGER);
            $checkStmt->bindValue(':image_url', $imageUrl, SQLITE3_TEXT);
            $exists = $checkStmt->execute();
            $existsRow = $exists ? $exists->fetchArray(SQLITE3_NUM) : false;
            if ($existsRow !== false) {
                $skippedExisting++;
                continue;
            }

            $caption = 'Room ' . $position;
            $rawJson = json_encode([
                'source_key' => (string)$rawKey,
                'parsed_name' => $parsedName,
                'caption' => $caption,
                'image_url' => $imageUrl,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $insertStmt->reset();
            $insertStmt->clear();
            $insertStmt->bindValue(':hotel_id', $hotelId, SQLITE3_INTEGER);
            $insertStmt->bindValue(':image_position', $position, SQLITE3_INTEGER);
            $insertStmt->bindValue(':url_position', $position, SQLITE3_INTEGER);
            $insertStmt->bindValue(':caption', $caption, SQLITE3_TEXT);
            $insertStmt->bindValue(':image_url', $imageUrl, SQLITE3_TEXT);
            $insertStmt->bindValue(':raw_json', (string)$rawJson, SQLITE3_TEXT);
            $ok = $insertStmt->execute();

            if ($ok === false) {
                throw new RuntimeException("Failed insert for hotel_id={$hotelId}, url={$imageUrl}\n");
            }

            $inserted++;
        }
    }
    $db->exec('COMMIT');
    $transactionActive = false;
} catch (Throwable $e) {
    if ($transactionActive) {
        $db->exec('ROLLBACK');
    }
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
} finally {
    if ($db instanceof SQLite3) {
        $db->close();
    }
}
        $insertStmt->clear();
        $insertStmt->bindValue(':hotel_id', $hotelId, SQLITE3_INTEGER);
        $insertStmt->bindValue(':image_position', $position, SQLITE3_INTEGER);
        $insertStmt->bindValue(':url_position', $position, SQLITE3_INTEGER);
        $insertStmt->bindValue(':caption', $caption, SQLITE3_TEXT);
        $insertStmt->bindValue(':image_url', $imageUrl, SQLITE3_TEXT);
        $insertStmt->bindValue(':raw_json', (string)$rawJson, SQLITE3_TEXT);
        $ok = $insertStmt->execute();

        if ($ok === false) {
            fwrite(STDERR, "Failed insert for hotel_id={$hotelId}, url={$imageUrl}\n");
            $db->exec('ROLLBACK');
            exit(1);
        }

        $inserted++;
    }


$db->exec('COMMIT');

$uf = fopen($unmatchedPath, 'w');
if ($uf !== false) {
    fputcsv($uf, ['raw_key', 'parsed_name', 'url_position', 'image_url'], ',', '"', '\\');
    foreach ($unmatched as $row) {
        fputcsv($uf, $row, ',', '"', '\\');
    }
    fclose($uf);
}

$afterCount = (int)$db->querySingle('SELECT COUNT(*) FROM link_hotel_images');

echo "Total JSON images: {$totalImages}\n";
echo "Inserted new rows: {$inserted}\n";
echo "Skipped existing rows: {$skippedExisting}\n";
echo "Unmatched images: " . count($unmatched) . "\n";
echo "Hotel matches by name: {$matchedByName}\n";
echo "Hotel matches by external ID fallback: {$matchedByExternal}\n";
echo "Final link_hotel_images count: {$afterCount}\n";
echo "Unmatched CSV: {$unmatchedPath}\n";
