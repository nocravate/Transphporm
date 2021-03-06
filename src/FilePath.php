<?php
namespace Transphporm;

class FilePath {
    private $baseDir;
    private $cwd;

    public function __construct(&$baseDir, $customBase = null) {
        $this->baseDir = &$baseDir;
        if ($customBase === null) $this->customBase = getcwd();
        else $this->customBase = rtrim($customBase, '/');
    }

    public function getFilePath($filePath = "") {
		if (isset($filePath[0]) && $filePath[0] == "/") return $this->customBase . $filePath;
		else return $this->baseDir . $filePath;
	}
}
