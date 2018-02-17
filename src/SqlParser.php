<?php

namespace IMI\DatabaseHelper;
/**
 * Class SqlParser
 */
class SqlParser {

    /**
     * Optimize a dump by converting single INSERTs per line to INSERTs with multiple lines
     * as well as wrapping everything into one transaction.
     * @param $fileName
     * @return string temporary filename
     */
    public static function optimize($fileName)
    {
        $in = fopen($fileName, 'r');
        $result = tempnam(sys_get_temp_dir(), 'dump') . '.sql';
        $out = fopen($result, 'w');
        fwrite($out, 'SET autocommit=0;' . "\n");
        $currentTable = '';
        $maxlen = 8 * 1024 * 1024; // 8 MB
        $len = 0;
        while ($line = fgets($in)) {
            if (strtolower(substr($line, 0, 11)) == 'insert into') {
                preg_match('/^insert into `(.*)` (\([^)]*\) )?values (.*);/i', $line, $m);
                if (count($m) < 3) { // fallback for very long lines or other cases where the preg_match fails
                    if ($currentTable != '') {
                        fwrite($out, ";\n");
                    }
                    fwrite($out, $line);
                    $currentTable = '';
                    continue;
                }
                $table = $m[1];
                $values = $m[3];
                if ($table != $currentTable || ($len > $maxlen - 1000)) {
                    if ($currentTable != '') {
                        fwrite($out, ";\n");
                    }
                    $currentTable = $table;
                    $insert = 'INSERT INTO `' . $table . '` VALUES ' . $values;
                    fwrite($out, $insert);
                    $len = strlen($insert);
                } else {
                    fwrite($out, ',' . $values);
                    $len += strlen($values) + 1;
                }
            } else {
                if ($currentTable != '') {
                    fwrite($out, ";\n");
                    $currentTable = '';
                }
                fwrite($out, $line);
            }
        }
        fwrite($out, ";\n");
        fwrite($out, 'COMMIT;' . "\n");
        fclose($in);
        fclose($out);
        return $result;
    }


}
