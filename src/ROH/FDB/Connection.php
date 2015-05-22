<?php

namespace ROH\FDB;

use Norm\Connection as NormConnection;
use Rhumsaa\Uuid\Uuid;
use Norm\Collection;

class Connection extends NormConnection
{
    function __construct(array $options = array()) {
        parent::__construct($options);

        if (!$this->option('dataDir')) {
            throw new \Exception('Data directory is not available "'.$this->option('dataDir').'"');
        }
    }
    public function query($collection, array $criteria = null)
    {
        return new Cursor($this->factory($collection), $criteria);
    }

    public function persist($collection, array $document)
    {
        if ($collection instanceof Collection) {
            $collection = $collection->getName();
        }

        $id = isset($document['$id']) ? $document['$id'] : Uuid::uuid1().'';
        $document = $this->marshall($document);
        $document['id'] = $id;

        $collectionDir = $this->option('dataDir').DIRECTORY_SEPARATOR.$collection.DIRECTORY_SEPARATOR;

        if (!is_dir($collectionDir)) {
            @unlink($collectionDir);
            mkdir($collectionDir, 0755, true);
        }

        file_put_contents($collectionDir.$document['id'].'.json', json_encode($document, JSON_PRETTY_PRINT));

        return $this->unmarshall($document);
    }

    protected function deltree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deltree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function remove($collection, $criteria = null)
    {
        if ($collection instanceof \Norm\Collection) {
            $collection = $collection->getName();
        }

        $collectionDir = $this->option('dataDir').DIRECTORY_SEPARATOR.$collection.DIRECTORY_SEPARATOR;

        if (func_num_args() === 1) {
            $this->deltree($collectionDir);
        } elseif ($criteria instanceof \Norm\Model) {
            $id = $criteria->getId();
            @unlink($collectionDir.$id.'.json');
        } else {
            throw new \Exception('Unimplemented yet!');
        }
    }

    public function fetch($cursor) {
        if ($cursor instanceof Cursor) {
            $query = array(
                'name' => $cursor->getCollection()->getName(),
                'criteria' => $cursor->getCriteria(),
                'limit' => $cursor->limit(),
                'skip' => $cursor->skip(),
                'sort' => $cursor->sort(),
            );
        } elseif (is_array($cursor)) {
            if (!isset($cursor['name'])) {
                throw new \Exception('Cannot fetch data without collection name!');
            }
        } else {
            throw new \Exception('Cannot fetch data without valid cursor!');
        }


        $collectionDir = $this->option('dataDir').DIRECTORY_SEPARATOR.$query['name'].DIRECTORY_SEPARATOR;

        $result = array();

        if (!is_dir($collectionDir)) {
            @unlink($collectionDir);
            mkdir($collectionDir, 0755, true);
            return $result;
        }


        if ($dh = opendir($collectionDir)) {
            $i = 0;
            $skip = 0;

            while (($file = readdir($dh)) !== false) {
                $filename = $collectionDir.$file;
                if (is_file($filename)) {
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    if (strtolower($ext) === 'json') {
                        $row = file_get_contents($filename);
                        $row = json_decode($row, true);

                        if ($this->isValidToFetch($row, $query['criteria'])) {

                            if (isset($query['skip']) && $query['skip'] > $skip) {
                                $skip++;
                                continue;
                            }

                            $result[] = $row;

                            $i++;
                            if (isset($query['limit']) && $query['limit'] == $i) {
                                break;
                            }
                        }
                    }
                }
            }
            closedir($dh);
        }

        return $result;
    }

    public function isValidToFetch($row, $criteria) {
        foreach ($criteria as $key => $value) {
            if ($key === '!or') {
                $valid = false;
                foreach ($value as $subCriteria) {
                    if ($this->isValidToFetch($row, $subCriteria)) {
                        $valid = true;
                        break;
                    }
                }
                if (!$valid) {
                    return false;
                }
            } else {
                $exploded = explode('!', $key);
                $key = $exploded[0];
                $operator = isset($exploded[1]) ? $exploded[1] : '=';

                if ($key === '$id') {
                    $key = 'id';
                } elseif ($key[0] === '$') {
                    $key = '_'.substr($key, 1);
                }

                switch($operator) {
                    case '=':
                        if ($value != $row[$key]) {
                            return false;
                        }
                        break;
                    default:
                        throw new \Exception("Operator '$operator' is not implemented yet!");
                }
            }
        }

        return true;
    }
}