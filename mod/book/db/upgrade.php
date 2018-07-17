<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Book module upgrade code
 *
 * @package    mod_book
 * @copyright  2009-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $keyTypeMap;
$keyTypeMap = [
    'primary' => XMLDB_KEY_PRIMARY,
    'unique' => XMLDB_KEY_UNIQUE,
    'foreign' => XMLDB_KEY_FOREIGN
];

/**
 * Book module upgrade task
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool always true
 */
function xmldb_book_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    $table = install_from_xml(__DIR__ . '/install.xml', 'book_progress');

    if ($oldversion <= 2018071700) {
        if (!$dbman->table_exists('book_progress')) {
            $dbman->create_table($table);
        }
    }

    return true;
}

function install_from_xml($xmlfilepath, $tablename) {
    global $keyTypeMap;

    $xml = new SimpleXMLElement(file_get_contents($xmlfilepath));
    $table = new xmldb_table($tablename);

    foreach ($xml->TABLES->TABLE as $tablexml){
        $tablenammeee = $tablexml->attributes()['NAME'];
        if($tablexml->attributes()['NAME'] == $tablename){
            foreach ($tablexml->FIELDS->FIELD as $field) {
                $table->addField(new xmldb_field(
                        $field->attributes()['NAME'],
//                        $field->attributes()['TYPE'],
                        1,
                        '10,0',
                        $field->attributes()['UNSIGNED'],
                        $field->attributes()['NOTNULL'],
                        $field->attributes()['SEQUENCE'],
                        NULL,
                        $field->attributes()['PREVIOUS']
                    )
                );
            }

            foreach ($tablexml->KEYS->KEY as $key) {
                $table->addKey(new xmldb_key(
                        $key->attributes()['NAME'],
                        $keyTypeMap[strval($key->attributes()['TYPE'])],
                        explode(',',$key->attributes()['FIELDS']),
                        $key->attributes()['REFTABLE'],
                        explode(',',$key->attributes()['REFFIELD'])
                    )
                );
            }
        }
    }

    return $table;
}
