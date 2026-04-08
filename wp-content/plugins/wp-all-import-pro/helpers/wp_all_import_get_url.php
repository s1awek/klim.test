<?php
/**
 * Reading large files from remote server
 * @ $filePath - file URL
 * return local path of copied file
 */

if (!function_exists('wp_all_import_get_url')) {

    function wp_all_import_get_url($filePath, $targetDir = FALSE, $contentType = FALSE, $contentEncoding = FALSE, $detect = FALSE) {

        $type = $contentType;

        $uploads = wp_upload_dir();

        $targetDir = (!$targetDir) ? wp_all_import_secure_file($uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::UPLOADS_DIRECTORY) : $targetDir;

        $tmpname = wp_unique_filename($targetDir, ($type and strlen(basename($filePath)) < 30) ? basename($filePath) : (string) time());

        $localPath = $targetDir . '/' . urldecode(sanitize_file_name($tmpname)) . ((!$type) ? '.tmp' : '');

		// Ensure we don't have a .php extension as it's often blocked on hosts in the uploads folder.
		$localPath = str_replace('.php','.tmp', $localPath);

        $is_valid = FALSE;

        $is_curl_download_only = apply_filters('wp_all_import_curl_download_only', true, $filePath);

        if ( ! $is_curl_download_only ){

            $file = @fopen($filePath, "rb");

            if (is_resource($file)) {

                $fp = @fopen($localPath, 'w');
                $first_chunk = TRUE;
                while (!@feof($file)) {
                    $chunk = @fread($file, 1024);
                    if (!$type && $first_chunk && (strpos($chunk, "<?") !== FALSE || strpos($chunk, "<rss") !== FALSE || strpos($chunk, "xmlns") !== FALSE)) {
                        $type = 'xml';
                    } elseif (!$type && $first_chunk) {
                        $type = 'csv';
                    }
                    $first_chunk = FALSE;
		                 @fwrite($fp, $chunk);
                }
                @fclose($file);
                @fclose($fp);

                $chunk = new PMXI_Chunk($localPath);

                if (!empty($chunk->options['element'])) {
                    $defaultXpath = "/" . $chunk->options['element'];
                    $is_valid = TRUE;
                }

                if ($is_valid) {

                    while ($xml = $chunk->read()) {

                        if (!empty($xml)) {

                            //PMXI_Import_Record::preprocessXml($xml);
                            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . "\n" . $xml;

                            $dom = new DOMDocument('1.0', 'UTF-8');
                            $old = libxml_use_internal_errors(TRUE);
                            $dom->loadXML($xml);
                            libxml_use_internal_errors($old);
                            $xpath = new DOMXPath($dom);
                            if (($elements = $xpath->query($defaultXpath)) and $elements->length) {
                                break;
                            }
                        }
                    }

                    if (empty($xml)) {
                        $is_valid = FALSE;
                    }
                }
                unset($chunk);
            }
        }

        if (!$is_valid) {

            $request = get_file_curl($filePath, $localPath);

            if ( ! is_wp_error($request) && false !== $request ) {

                if ( ! $type ) {
                    // Check magic bytes to detect binary file formats before text-sniffing.
                    $magic = @file_get_contents($localPath, false, null, 0, 8);
                    if ($magic !== false && strlen($magic) >= 2) {
                        if ($magic[0] === "\x1f" && $magic[1] === "\x8b") {
                            $type = 'gz';
                        } elseif (strlen($magic) >= 4 && $magic[0] === "\x50" && $magic[1] === "\x4b" && $magic[2] === "\x03" && $magic[3] === "\x04") {
                            // ZIP magic bytes — check if it's actually an XLSX (OOXML) file
                            // by looking for the Excel-specific workbook entry inside the ZIP.
                            $type = 'zip';
                            if (class_exists('ZipArchive')) {
                                $zip_check = new ZipArchive();
                                if ($zip_check->open($localPath) === true) {
                                    if ($zip_check->locateName('xl/workbook.xml') !== false) {
                                        $type = 'xlsx';
                                    }
                                    $zip_check->close();
                                }
                            }
                        } elseif (strlen($magic) >= 8 && $magic[0] === "\xD0" && $magic[1] === "\xCF" && $magic[2] === "\x11" && $magic[3] === "\xE0"
                            && $magic[4] === "\xA1" && $magic[5] === "\xB1" && $magic[6] === "\x1A" && $magic[7] === "\xE1") {
                            // OLE2 Compound Document — also matches .doc/.ppt, but those are
                            // uncommon in import feeds. PhpSpreadsheet will reject non-Excel files.
                            $type = 'xls';
                        }
                    }
                }
                if ( ! $type ) {
                    $file = @fopen($localPath, "rb");
                    if (is_resource($file)) {
                        $chunk = @fread($file, 1024);
                        if ($chunk !== false && (strpos($chunk, "<?") !== FALSE || strpos($chunk, "<rss") !== FALSE || strpos($chunk, "xmlns") !== FALSE)) {
                            $type = 'xml';
                        } else {
                            $type = 'csv';
                        }
                        @fclose($file);
                    }
                }
            } else {
                return $request;
            }

        }

        if (!preg_match('%\W(' . $type . ')$%i', basename($localPath))) {
            if (@rename($localPath, $localPath . '.' . $type)) {
                $localPath = $localPath . '.' . $type;
            }
        }

        return ($detect) ? array(
            'type' => $type,
            'localPath' => $localPath
        ) : $localPath;
    }
}	