<?php
/**
 * Copyright 2024 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_postgresql_alter_sequence]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateDatabaseDdlRequest;
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Result;

/**
 * Alters a sequence.
 * Example:
 * ```
 * pg_alter_sequence($projectId, $instanceId, $databaseId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function pg_alter_sequence(
    string $projectId,
    string $instanceId,
    string $databaseId
): void {
    $databaseAdminClient = new DatabaseAdminClient();
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);
    $transaction = $database->transaction();
    $databaseName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);
    $statement = 'ALTER SEQUENCE Seq SKIP RANGE 1000 5000000';
    $request = new UpdateDatabaseDdlRequest([
        'database' => $databaseName,
        'statements' => [$statement]
    ]);

    $operation = $databaseAdminClient->updateDatabaseDdl($request);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf(
        'Altered Seq sequence to skip an inclusive range between 1000 and 5000000' .
        PHP_EOL
    );

    $res = $transaction->execute(
        'INSERT INTO Customers (CustomerName) VALUES ' .
        "('Lea'), ('Catalina'), ('Smith') RETURNING CustomerId"
    );
    $rows = $res->rows(Result::RETURN_ASSOCIATIVE);

    foreach ($rows as $row) {
        printf('Inserted customer record with CustomerId: %d %s',
            $row['customerid'],
            PHP_EOL
        );
    }
    $transaction->commit();

    printf(sprintf(
        'Number of customer records inserted is: %d %s',
        $res->stats()['rowCountExact'],
        PHP_EOL
    ));
}
// [END spanner_postgresql_alter_sequence]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
