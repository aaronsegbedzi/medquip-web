<?php

namespace App\Helpers;

class InstalledFileManager
{

    public function create()
    {
        file_put_contents(storage_path('installed'), '1.2.0');
    }

    public function update()
    {
        return $this->create();
    }
}
