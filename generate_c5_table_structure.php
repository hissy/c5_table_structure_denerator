<?php
use Aura\Cli\CliFactory;
use Aura\Cli\Status;
use Fusonic\SpreadsheetExport\Spreadsheet;
use Fusonic\SpreadsheetExport\ColumnTypes\TextColumn;
use Fusonic\SpreadsheetExport\Writers\OdsWriter;

require 'vendor/autoload.php';

$cli_factory = new CliFactory;
$context = $cli_factory->newContext($GLOBALS);
$getopt = $context->getopt(array());
$stdio = $cli_factory->newStdio();

$name = $getopt->get(1);
if (!$name) {
    // print an error
    $stdio->errln("Please give a file name of xml file.");
    exit(Status::USAGE);
}

$sx = simplexml_load_file($name);

foreach ($sx->table as $x) {
    // Instantiate new spreadsheet
    $export = new Spreadsheet();
    $export->addColumn(new TextColumn("Field Name"));
    $export->addColumn(new TextColumn("Type"));
    $export->addColumn(new TextColumn("Length"));
    $export->addColumn(new TextColumn("Unsigned"));
    $export->addColumn(new TextColumn("Not Null"));
    $export->addColumn(new TextColumn("Default Value"));
    $export->addColumn(new TextColumn("Key"));
    $export->addColumn(new TextColumn("Autoincrement"));
    
    foreach ($x->field as $field) {
        
        $name = (string) $field['name'];
        $type = (string) $field['type'];
        $size = (string) $field['size'];
        
        if ($type == 'L') {
            $type = 'boolean';
        }
        if ($type == 'I1') {
            if ($size != '' && $size > 1) {
                $type = 'smallint';
            } else {
                $type = 'boolean';
            }
        }
        if ($type == 'I2') {
            $type = 'smallint';
        }
        if ($type == 'I4') {
            $type = 'integer';
        }
        if ($type == 'I') {
            if ($size === '1') {
                $type = 'boolean';
            }
            if ($size != '' && $size < 5) {
                $type = 'smallint';
            }
            $type = 'integer';
        }
        if ($type == 'I8') {
            $type = 'bigint';
        }
        if ($type == 'C') {
            $type = 'string';
        }
        if ($type == 'F') {
            $type = 'float';
        }
        if ($type == 'X') {
            $type = 'text';
        }
        if ($type == 'XL') {
            $type = 'text';
        }
        if ($type == 'C2') {
            $type = 'string';
        }
        if ($type == 'X2') {
            $type = 'text';
        }
        if ($type == 'T') {
            $type = 'datetime';
        }
        if ($type == 'TS') {
            $type = 'datetime';
        }
        if ($type == 'D') {
            $type = 'date';
        }
        if ($type == 'N') {
            $type = 'decimal';
        }
        if ($type == 'B') {
            $type = 'blob';
        }
        
        $unsigned = (isset($field->UNSIGNED) || $field->unsigned) ? 'yes' : '';
        $notnull = (isset($field->NOTNULL) || $field->notnull) ? 'yes' : '';
        $default = (isset($field->DEFAULT)) ? $field->DEFAULT : '';
        $default = (isset($field->default)) ? $field->default : $default;
        $default_value = ($default) ? $default['value'] : '';
        $key = (isset($field->KEY) || $field->key) ? 'yes' : '';
        $autoincrement = (isset($field->AUTOINCREMENT) || $field->autoincrement) ? 'yes' : '';
        
        $export->addRow(array($name, $type, $size, $unsigned, $notnull, $default_value, $key, $autoincrement));
    }
    
    $writer = new OdsWriter();
    $writer->includeColumnHeaders = true;
    $export->save($writer, "./tmp/".$x['name']);
}

exit(Status::SUCCESS);