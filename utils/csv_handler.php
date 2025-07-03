<?php
class CSVHandler {
    /**
     * Import credentials from CSV content
     */
    public static function importFromCSV($csvContent) {
        $lines = explode("\n", trim($csvContent));
        $imported = 0;
        $errors = [];
        $credentialsToAdd = [];
        
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Skip header line if it exists
            if ($lineNum === 0 && (stripos($line, 'instagram') !== false || stripos($line, 'password') !== false)) {
                continue;
            }
            
            $parts = str_getcsv($line);
            
            if (count($parts) < 1) {
                $errors[] = "Line " . ($lineNum + 1) . ": Invalid format";
                continue;
            }
            
            $instagramId = trim($parts[0]);
            $password = isset($parts[1]) && !empty(trim($parts[1])) ? trim($parts[1]) : DEFAULT_PASSWORD;
            
            if (empty($instagramId)) {
                $errors[] = "Line " . ($lineNum + 1) . ": Instagram ID is empty";
                continue;
            }
            
            // Check for duplicates in existing credentials
            if (FileOperations::credentialExists($instagramId)) {
                $errors[] = "Line " . ($lineNum + 1) . ": Instagram ID '{$instagramId}' already exists";
                continue;
            }
            
            // Check for duplicates in current import batch
            $duplicateInBatch = false;
            foreach ($credentialsToAdd as $existingCred) {
                if ($existingCred['instagram_id'] === $instagramId) {
                    $duplicateInBatch = true;
                    break;
                }
            }
            
            if ($duplicateInBatch) {
                $errors[] = "Line " . ($lineNum + 1) . ": Duplicate Instagram ID '{$instagramId}' in import file";
                continue;
            }
            
            $credentialsToAdd[] = [
                'instagram_id' => $instagramId,
                'password' => $password
            ];
            $imported++;
        }
        
        // Bulk add valid credentials
        if (!empty($credentialsToAdd)) {
            FileOperations::bulkAddCredentials($credentialsToAdd);
        }
        
        return [
            'imported' => $imported,
            'errors' => $errors
        ];
    }
    
    /**
     * Import credentials from TXT content
     */
    public static function importFromTXT($txtContent) {
        $lines = explode("\n", trim($txtContent));
        $imported = 0;
        $errors = [];
        $credentialsToAdd = [];
        
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = preg_split('/\s+/', $line, 2);
            
            if (count($parts) < 1) {
                $errors[] = "Line " . ($lineNum + 1) . ": Invalid format";
                continue;
            }
            
            $instagramId = trim($parts[0]);
            $password = isset($parts[1]) && !empty(trim($parts[1])) ? trim($parts[1]) : DEFAULT_PASSWORD;
            
            if (empty($instagramId)) {
                $errors[] = "Line " . ($lineNum + 1) . ": Instagram ID is empty";
                continue;
            }
            
            // Check for duplicates in existing credentials
            if (FileOperations::credentialExists($instagramId)) {
                $errors[] = "Line " . ($lineNum + 1) . ": Instagram ID '{$instagramId}' already exists";
                continue;
            }
            
            // Check for duplicates in current import batch
            $duplicateInBatch = false;
            foreach ($credentialsToAdd as $existingCred) {
                if ($existingCred['instagram_id'] === $instagramId) {
                    $duplicateInBatch = true;
                    break;
                }
            }
            
            if ($duplicateInBatch) {
                $errors[] = "Line " . ($lineNum + 1) . ": Duplicate Instagram ID '{$instagramId}' in import file";
                continue;
            }
            
            $credentialsToAdd[] = [
                'instagram_id' => $instagramId,
                'password' => $password
            ];
            $imported++;
        }
        
        // Bulk add valid credentials
        if (!empty($credentialsToAdd)) {
            FileOperations::bulkAddCredentials($credentialsToAdd);
        }
        
        return [
            'imported' => $imported,
            'errors' => $errors
        ];
    }
    
    /**
     * Export credentials to CSV format
     */
    public static function exportToCSV($credentials) {
        $csv = "instagram_id,password,date_added\n";
        
        foreach ($credentials as $cred) {
            $csv .= '"' . str_replace('"', '""', $cred['instagram_id']) . '",';
            $csv .= '"' . str_replace('"', '""', $cred['password']) . '",';
            $csv .= '"' . str_replace('"', '""', $cred['date_added']) . '"';
            $csv .= "\n";
        }
        
        return $csv;
    }
    
    /**
     * Export credentials to TXT format
     */
    public static function exportToTXT($credentials) {
        $txt = "# Instagram Credentials Export\n";
        $txt .= "# Format: instagram_id password date_added\n";
        $txt .= "# Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($credentials as $cred) {
            $txt .= $cred['instagram_id'] . ' ' . $cred['password'] . ' ' . $cred['date_added'] . "\n";
        }
        
        return $txt;
    }
}
?>
