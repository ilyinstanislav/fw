<?php

/**
 * Class MigrationsController
 */
class MigrationsController extends Controller
{
    function actionMigrate()
    {
        $files = $this->getMigrationFiles();
        $success = true;
        $count = count($files);
        $complete = 0;
        foreach ($files as $file) {
            if (!$this->migrate($file)) {
                $success = false;
                break;
            }
            $complete++;
        }

        echo $this->sendResult([
            'success' => $success,
            'message' => "complete $complete from $count migrations"
        ]);
    }

    /**
     * @return array|false
     * @throws Exception
     */
    protected function getMigrationFiles()
    {
        $allFiles = glob(ROOT . 'migrations/' . '*.sql');

        $query = sprintf('show tables from `%s` like "%s"', App::getInstance()->config['db']['dbname'], 'migrations');
        $data = $this->db()->query($query);
        $firstMigration = !$data->numRows();
        if ($firstMigration) {
            return $allFiles;
        }

        $versionsFiles = array();
        $query = 'select name from migrations';

        $data = $this->db()->query($query)->rows();
        foreach ($data as $row) {
            array_push($versionsFiles, ROOT . 'migrations/' . $row->name);
        }

        return array_diff($allFiles, $versionsFiles);
    }

    /**
     * @param $file
     * @return bool
     * @throws Exception
     */
    protected function migrate($file)
    {
        $query = file_get_contents($file);

        if ($this->db()->query($query)->exec()) {
            $baseName = basename($file);
            $query = sprintf('insert into migrations (`name`) values("%s")', $baseName);
            return $this->db()->query($query)->exec();
        }
        return false;
    }
}