<?PHP
/**
 * @author ddcai
 * @desc 文件操作类
 * 
 * expample:
 * 
 * mkDir('a/1/2/3');          测试建立文件夹 建一个a/1/2/3文件夹
 * touch('b/1/2/3');          测试建立文件        在b/1/2/文件夹下面建一个3文件
 * touch('b/1/2/3.exe');        测试建立文件        在b/1/2/文件夹下面建一个3.exe文件
 * cp('b','d/e');      测试复制文件夹    建立一个d/e文件夹，把b文件夹下的内容复制进去
 * cp('b/1/2/3.exe','b/b/3.exe');  测试复制文件      建立一个b/b文件夹，并把b/1/2文件夹中的3.exe文件复制进去
 * mv('a/','b/c');                    测试移动文件夹   建立一个b/c文件夹,并把a文件夹下的内容移动进去，并删除a文件夹
 * mv('b/1/2/3.exe','b/d/3.exe');  测试移动文件      建立一个b/d文件夹，并把b/1/2中的3.exe移动进去          
 * rm('b/d/3.exe');               测试删除文件  删除b/d/3.exe文件
 * rm('d');                     测试删除文件夹  删除d文件夹
 * 
 * write('b/c/d', 'test', 'ab');          测试写入一个文件
 * while($rs = readLine('b/c/d')) {echo $rs}    测试从文件中读取一行
 * echo readAll('b/c/d')              测试从文件中读取所有
 */
class zdeFile{
    /**
     * 构造函数
     *
     */
    public function __construct() {
        return true;
    }
    
    /**
     * 建立文件夹
     *
     * @param   string $aimUrl
     * @return  viod
     */
    function mkDir($aimUrl) {
        $aimUrl = str_replace('\\', '/', $aimUrl);
        $aimDir = '';
        $arr = explode('/', $aimUrl);
        foreach ($arr as $str) {
			if($str!=""){
				$aimDir .= $str . '/';
				if (!$this->isExists($aimDir)) {
					mkdir($aimDir);
				}
			}
        }
    }

    /**
     * 建立文件
     *
     * @param   string $aimUrl 
     * @param   boolean    $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
    function touch($aimUrl, $overWrite = false) {
        if ($this->isExists($aimUrl) && $overWrite == false) {
            return false;
        } elseif ($this->isExists($aimUrl) && $overWrite == true) {
            $this->rmFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        if (!$this->isExists($aimDir)) {
            $this->mkDir($aimDir);
        }
        touch($aimUrl);
        return true;
    }

    /**
     * 移动文件(或文件夹)
     *
     * @param string $filePath
     * @param string $aimPath
     * @param boolean $overWrite
     * @return boolean
     */
    function mv($filePath, $aimPath, $overWrite = false) {
        if ($this->isDir($filePath)) {
            return $this->mvDir($filePath, $aimPath, $overWrite);
        } else {
            return $this->mvFile($filePath, $aimPath, $overWrite);
        }
    }

    /**
     * 复制文件(或文件夹)
     *
     * @param string $filePath
     * @param string $aimPath
     * @param boolean $overWrite
     * @return boolean
     */
    function cp($filePath, $aimPath, $overWrite = false) {
        if ($this->isDir($filePath)) {
            return $this->cpDir($filePath, $aimPath, $overWrite);
        } else {
            return $this->cpFile($filePath, $aimPath, $overWrite);
        }
    }

    /**
     * 删除文件(或文件夹)
     *
     * @param string $filePath
     * @return boolean
     */
    function rm($filePath) {
        if ($this->isDir($filePath)) {
            return $this->rmDir($filePath);
        } else {
            return $this->rmFile($filePath);
        }
    }

    /**
     * 判断当前文件是否是一个文件夹
     *
     * @param string $path
     * @return boolean
     */
    function isDir($path) {
        return @is_dir($path);
    }

    /**
     * 判断当前文件是否存在
     *
     * @param string $path
     * @return boolean
     */
    function isExists($path) {
        return @file_exists($path);
    }

    /**
     * 将数据写入(或追加入)文件
     *
     * @param string $file
     * @param string $content
     * @param string $type
     * @return boolean
     */
    function write($file, $content, $append = false) {
        if (!$this->isExists(dirname($file))) {
            $this->mkDir(dirname($file));
        }
        $type = $append ? 'ab' : 'wb';
        
        // 如果无法写入文件，则记录错误
        if (!$fp = @fopen($file, $type)) {
            return false;
        }
        @flock($fp, LOCK_EX);         // 独占锁定
        $ok = @fwrite($fp, $content);   // 写入
        @flock($fp, LOCK_UN);         // 解锁
        @fclose($fp);               // 关闭
        return $ok;
    }

    /**
     * 读取文件中的一行数据
     *
     * @param string $file
     * @param string $size
     * @return string
     */
    function readLine($file, $size = 4096) {
        static $fileArr = array();
        
        if (!$fileArr[$file]) {
            $fileArr[$file] = @fopen($file, "r");
        }
        $fp = $fileArr[$file];

        if ($fp && !feof($fp)) {
            return fgets($fp, $size);
        }
        fclose($fp);
        unset($fileArr[$file]);
        return false;
    }

    /**
     * 读取文件中的所有数据
     *
     * @param string $file
     * @param string $size
     * @return string
     */
    function readAll($file) {
		if (!$this->isExists($file)) {
            return "";
        }else{
        	return file_get_contents($file);
		}
    }


    /**
     * 移动文件夹
     *
     * @param   string $oldDir
     * @param   string $aimDir
     * @param   boolean    $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
    private function mvDir($oldDir, $aimDir, $overWrite) {
        $aimDir = str_replace('\\', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        $oldDir = str_replace('\\', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!$this->isExists($aimDir)) {
            $this->mkDir($aimDir);
        }
        @$dirHandle = opendir($oldDir);
        if (!$dirHandle) {
            return false;
        }
        while(false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir.$file)) {
                $this->mvFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                $this->mvDir($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
        closedir($dirHandle);
        return rmdir($oldDir);
    }

    /**
     * 移动文件
     *
     * @param   string $fileUrl
     * @param   string $aimUrl
     * @param   boolean    $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
    private function mvFile($fileUrl, $aimUrl, $overWrite) {
        if (!$this->isExists($fileUrl)) {
            return false;
        }
        if ($this->isExists($aimUrl) && $overWrite = false) {
            return false;
        } elseif ($this->isExists($aimUrl) && $overWrite = true) {
            $this->rmFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        $this->mkDir($aimDir);
        rename($fileUrl, $aimUrl);
        return true;
    }

    /**
     * 删除文件夹
     *
     * @param   string $aimDir
     * @return  boolean
     */
    private function rmDir($aimDir) {
        $aimDir = str_replace('\\', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir.'/';
        if (!is_dir($aimDir)) {
            return false;
        }
        $dirHandle = opendir($aimDir);
        while(false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($aimDir.$file)) {
                $this->rmFile($aimDir . $file);
            } else {
                $this->rmDir($aimDir . $file);
            }
        }
        closedir($dirHandle);
        return rmdir($aimDir);
    }

    /**
     * 删除文件
     *
     * @param   string $aimUrl
     * @return  boolean
     */
    private function rmFile($aimUrl) {
        if ($this->isExists($aimUrl)) {
            unlink($aimUrl);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 复制文件夹
     *
     * @param   string $oldDir
     * @param   string $aimDir
     * @param   boolean    $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
    private function cpDir($oldDir, $aimDir, $overWrite) {
        $aimDir = str_replace('\\', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir.'/';
        $oldDir = str_replace('\\', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir.'/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!$this->isExists($aimDir)) {
            $this->mkDir($aimDir);
        }
        $dirHandle = opendir($oldDir);
        while(false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir . $file)) {
                $this->cpFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                $this->cpDir($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
        return closedir($dirHandle);
    }

    /**
     * 复制文件
     *
     * @param   string $fileUrl
     * @param   string $aimUrl
     * @param   boolean    $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
    private function cpFile($fileUrl, $aimUrl, $overWrite) {
        if (!$this->isExists($fileUrl)) {
            return false;
        }
        if ($this->isExists($aimUrl) && $overWrite == false) {
            return false;
        } elseif ($this->isExists($aimUrl) && $overWrite == true) {
            $this->rmFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        $this->mkDir($aimDir);
        copy($fileUrl, $aimUrl);
        return true;
    }
	/**
     * 读取目录下文件
     *
     * @param   string     $dir 目录路径
     * @param   boolean    $child 该参数控制是否读取子目录文件开关 默认开
     * @return  array      $Filearr
	 * add by earthliu 2011-3-16
     */
	 function findDir($dir, &$Filearr, $child = true){
		'/' != substr($dir,-1) && $dir = $dir.'/';  //添加/
	 	if($child){
			$files = scandir($dir);
			if($files !== false){
				foreach($files as $value){
					$path = $dir.$value;
					if($value == '.' || $value == '..') continue;
					if(is_dir($path)){
						$this->findDir($path, &$Filearr, true);
					}else{
						$Filearr[] = $path;
					}
				}
			}
			
		}else{
			$files = scandir($dir);
			$Filearr = array_diff($files,array('.','..'));
			
		}
	 }
	 
	 function getDir($dir, $child = true){
	 	$Filearr = array();
		$this->findDir($dir,$Filearr,$child);
		return $Filearr;
	 }
}

?>