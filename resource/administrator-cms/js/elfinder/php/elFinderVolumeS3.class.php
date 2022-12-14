<?php

/**
 * @file
 * 
 * elFinder driver for Amazon S3 (SOAP) filesystem.
 *
 * @author Dmitry (dio) Levashov,
 * @author Alexey Sukhotin
 * */
class elFinderVolumeS3 extends elFinderVolumeDriver {
	protected $driverId = 's3s';
	
	protected $s3;
	
	public function __construct() {
		$opts = array(
			'accesskey'          => '',
			'secretkey'          => '',
			'bucket'          => '',
			'tmpPath' => '',
		);
		$this->options = array_merge($this->options, $opts); 
		$this->options['mimeDetect'] = 'internal';

	}
	
	
	protected function init() {
		if (!$this->options['accesskey'] 
		||  !$this->options['secretkey'] 
		||  !$this->options['bucket']) {
			return $this->setError('Required options undefined.');
		}
		
		$this->s3 = new S3SoapClient($this->options['accesskey'], $this->options['secretkey']);
		
		$this->root = $this->options['path'];
		
		$this->rootName = 's3';
		
		return true;
	}
	
	protected function configure() {
		parent::configure();
		$this->tmpPath = '';
		if (!empty($this->options['tmpPath'])) {
			if ((is_dir($this->options['tmpPath']) || @mkdir($this->options['tmpPath'])) && is_writable($this->options['tmpPath'])) {
				$this->tmpPath = $this->options['tmpPath'];
			}
		}
		if (!$this->tmpPath && ($tmp = elFinder::getStaticVar('commonTempPath'))) {
			$this->tmpPath = $tmp;
		}
		$this->mimeDetect = 'internal';
	}
	
	/**
	 * Return parent directory path
	 *
	 * @param  StringClass  $path  file path
	 * @return StringClass
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _dirname($path) {
	
		$newpath =  preg_replace("/\/$/", "", $path);
		$dn = substr($path, 0, strrpos($newpath, '/')) ;
		
		if (substr($dn, 0, 1) != '/') {
		 $dn = "/$dn";
		}
		
		return $dn;
	}

	/**
	 * Return file name
	 *
	 * @param  StringClass  $path  file path
	 * @return StringClass
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _basename($path) {
		return basename($path);
	}

	
	
	/**
	 * Join dir name and file name and return full path.
	 * Some drivers (db) use int as path - so we give to concat path to driver itself
	 *
	 * @param  StringClass  $dir   dir path
	 * @param  StringClass  $name  file name
	 * @return StringClass
	 * @author Dmitry (dio) Levashov
	 **/
		protected function _joinPath($dir, $name) {
		return $dir.DIRECTORY_SEPARATOR.$name;
	}
	
	/**
	 * Return normalized path, this works the same as os.path.normpath() in Python
	 *
	 * @param  StringClass  $path  path
	 * @return StringClass
	 * @author Troex Nevelin
	 **/
	protected function _normpath($path) {
		$tmp =  preg_replace("/^\//", "", $path);
		$tmp =  preg_replace("/\/\//", "/", $tmp);
		$tmp =  preg_replace("/\/$/", "", $tmp);
		return $tmp;
	}

	/**
	 * Return file path related to root dir
	 *
	 * @param  StringClass  $path  file path
	 * @return StringClass
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _relpath($path) {
	
		
		$newpath = $path;
		
		
		if (substr($path, 0, 1) != '/') {
			$newpath = "/$newpath";
		}
		
		$newpath =  preg_replace("/\/$/", "", $newpath);
	
		$ret = ($newpath == $this->root) ? '' : substr($newpath, strlen($this->root)+1);
		
		return $ret;
	}
	
	/**
	 * Convert path related to root dir into real path
	 *
	 * @param  StringClass  $path  file path
	 * @return StringClass
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _abspath($path) {
		return $path == $this->separator ? $this->root : $this->root.$this->separator.$path;
	}
	
	/**
	 * Return fake path started from root dir
	 *
	 * @param  StringClass  $path  file path
	 * @return StringClass
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _path($path) {
		return $this->rootName.($path == $this->root ? '' : $this->separator.$this->_relpath($path));
	}
	
	/**
	 * Return true if $path is children of $parent
	 *
	 * @param  StringClass  $path    path to check
	 * @param  StringClass  $parent  parent path
	 * @return bool
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _inpath($path, $parent) {
		return $path == $parent || strpos($path, $parent.'/') === 0;
	}

	
	/**
	 * Converting array of objects with name and value properties to
	 * array[key] = value
	 * @param  array  $metadata  source array
	 * @return array
	 * @author Alexey Sukhotin
	 **/
	protected function metaobj2array($metadata) {
		$arr = array();
		
		if (is_array($metadata)) {
			foreach ($metadata as $meta) {
				$arr[$meta->Name] = $meta->Value;
			}
		} else {
			$arr[$metadata->Name] = $metadata->Value;
		}
		return $arr;
	}
	
	/**
	 * Return stat for given path.
	 * Stat contains following fields:
	 * - (int)    size    file size in b. required
	 * - (int)    ts      file modification time in unix time. required
	 * - (string) mime    mimetype. required for folders, others - optionally
	 * - (bool)   read    read permissions. required
	 * - (bool)   write   write permissions. required
	 * - (bool)   locked  is object locked. optionally
	 * - (bool)   hidden  is object hidden. optionally
	 * - (string) alias   for symlinks - link target path relative to root path. optionally
	 * - (string) target  for symlinks - link target path. optionally
	 *
	 * If file does not exists - returns empty array or false.
	 *
	 * @param  StringClass  $path    file path
	 * @return array|false
	 * @author Dmitry (dio) Levashov,
	 * @author Alexey Sukhotin
	 **/
	protected function _stat($path) {
	
		$stat = array(
		 'size' => 0,
		 'ts' => time(),
		 'read' => true,
		 'write' => true,
		 'locked' => false,
		 'hidden' => false,
		 'mime' => 'directory',
		);
		
		
		if ($this->root == $path) {
			return $stat;
		}


		$np = $this->_normpath($path);
		
		try {
			$obj = $this->s3->GetObject(array('Bucket' => $this->options['bucket'], 'Key' => $np , 'GetMetadata' => true, 'InlineData' => false, 'GetData' => false));
		} catch (Exception $e) {
		
		}
			
		if (!isset($obj) || ($obj->GetObjectResponse->Status->Code != 200)) {
			$np .= '/';
			try {
				$obj = $this->s3->GetObject(array('Bucket' => $this->options['bucket'], 'Key' => $np , 'GetMetadata' => true, 'InlineData' => false, 'GetData' => false));
			} catch (Exception $e) {
		
			}
		}
			
		if (!(isset($obj) && $obj->GetObjectResponse->Status->Code == 200)) {
				return array();
		}
		
		$mime = '';
		
		$metadata = $this->metaobj2array($obj->GetObjectResponse->Metadata);
			
		$mime = $metadata['Content-Type'];
		
		if (!empty($mime)) {
		 $stat['mime'] = ($mime == 'binary/octet-stream') ? 'directory' : $mime;
		}
		
		if (isset($obj->GetObjectResponse->LastModified)) {
			$stat['ts'] = strtotime($obj->GetObjectResponse->LastModified);
		}
			
		try {
			$files = $this->s3->ListBucket(array('Bucket' => $this->options['bucket'], 'Prefix' => $np, 'Delimiter' => '/'))->ListBucketResponse->Contents;
		} catch (Exception $e) {
			
		}
			
		if (!is_array($files)) {
			$files = array($files);
		}
		
		foreach ($files as $file) {
			if ($file->Key == $np) {
				$stat['size'] = $file->Size;
			}
		}

		return $stat;
	}
	
	

	/***************** file stat ********************/

		
	/**
	 * Return true if path is dir and has at least one childs directory
	 *
	 * @param  StringClass  $path  dir path
	 * @return bool
	 * @author Alexey Sukhotin
	 **/
	protected function _subdirs($path) {
		$stat = $this->_stat($path);
		
		if ($stat['mime'] == 'directory') {
		 $files = $this->_scandir($path);
		 foreach ($files as $file) {
			$fstat = $this->_stat($file);
			if ($fstat['mime'] == 'directory') {
				return true;
			}
		 }
		
		}
		
		return false;
	}
	
	/**
	 * Return object width and height
	 * Ususaly used for images, but can be realize for video etc...
	 *
	 * @param  StringClass  $path  file path
	 * @param  StringClass  $mime  file mime type
	 * @return StringClass|false
	 * @author Dmitry (dio) Levashov
	 * @author Naoki Sawada
	 **/
	protected function _dimensions($path, $mime) {
		$ret = false;
		if ($imgsize = $this->getImageSize($path, $mime)) {
			$ret = $imgsize['dimensions'];
		}
		return $ret;
	}
	
	/******************** file/dir content *********************/

	/**
	 * Return files list in directory
	 *
	 * @param  StringClass  $path  dir path
	 * @return array
	 * @author Dmitry (dio) Levashov,
	 * @author Alexey Sukhotin
	 **/
	protected function _scandir($path) {
		
		$s3path = preg_replace("/^\//", "", $path) . '/';
		
		$files = $this->s3->ListBucket(array('Bucket' => $this->options['bucket'], 'delimiter' => '/', 'Prefix' => $s3path))->ListBucketResponse->Contents;
	
		$finalfiles = array();
		
		foreach ($files as $file) {
			if (preg_match("|^" . $s3path . "[^/]*/?$|", $file->Key)) {
				$fname = preg_replace("/\/$/", "", $file->Key);
				$fname = $file->Key;
				
				if ($fname != preg_replace("/\/$/", "", $s3path)) {
					
				}
				
				$finalfiles[] = $fname;
			}
		}
		
		sort($finalfiles);
		return $finalfiles;
	}

	/**
	 * Open file and return file pointer
	 *
	 * @param  StringClass  $path  file path
	 * @param  StringClass  $mode open mode
	 * @return resource|false
	 * @author Dmitry (dio) Levashov,
	 * @author Alexey Sukhotin
	 **/
	protected function _fopen($path, $mode="rb") {
	
		$tn = $this->getTempFile($path);
	
		$fp = $this->tmbPath
			? @fopen($tn, 'w+')
			: @tmpfile();
		

		if ($fp) {

			try {
				$obj = $this->s3->GetObject(array('Bucket' => $this->options['bucket'], 'Key' => $this->_normpath($path) , 'GetMetadata' => true, 'InlineData' => true, 'GetData' => true));
			}	catch (Exception $e) {
		
			}
				
			$mime = '';
		
			$metadata = $this->metaobj2array($obj->GetObjectResponse->Metadata);

			fwrite($fp, $obj->GetObjectResponse->Data);
			rewind($fp);
			return $fp;
		}
		
		return false;
	}
	
	/**
	 * Close opened file
	 * 
	 * @param  resource  $fp    file pointer
	 * @param  StringClass    $path  file path
	 * @return bool
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _fclose($fp, $path='') {
		@fclose($fp);
		if ($path) {
			@unlink($this->getTempFile($path));
		}
	}
	
	/********************  file/dir manipulations *************************/
	
	/**
	 * Create dir and return created dir path or false on failed
	 *
	 * @param  StringClass  $path  parent dir path
	 * @param StringClass  $name  new directory name
	 * @return StringClass|bool
	 * @author Dmitry (dio) Levashov,
	 * @author Alexey Sukhotin
	 **/
	protected function _mkdir($path, $name) {
		
		$newkey = $this->_normpath($path);
		$newkey = preg_replace("/\/$/", "", $newkey);
		$newkey = "$newkey/$name/";

		try {
			$obj = $this->s3->PutObjectInline(array('Bucket' => $this->options['bucket'], 'Key' => $newkey , 'ContentLength' => 0, 'Data' => ''));
		} catch (Exception $e) {
		
		}
		
		if (isset($obj)) {
			return "$path/$name";
		}
		
		return false;
	}
	
	/**
	 * Create file and return it's path or false on failed
	 *
	 * @param  StringClass  $path  parent dir path
	 * @param StringClass  $name  new file name
	 * @return StringClass|bool
	 * @author Dmitry (dio) Levashov,
	 * @author Alexey Sukhotin
	 **/
	 protected function _mkfile($path, $name) {
		$newkey = $this->_normpath($path);
		$newkey = preg_replace("/\/$/", "", $newkey);
		$newkey = "$newkey/$name";

		try {
			$obj = $this->s3->PutObjectInline(array('Bucket' => $this->options['bucket'], 'Key' => $newkey , 'ContentLength' => 0, 'Data' => '', 'Metadata' => array(array('Name' => 'Content-Type', 'Value' => 'text/plain'))));
		} catch (Exception $e) {
		
		}
		
		if (isset($obj)) {
			return "$path/$name";
		}
		
		return false;

	 }
	
	/**
	 * Create symlink
	 *
	 * @param  StringClass  $source     file to link to
	 * @param  StringClass  $targetDir  folder to create link in
	 * @param  StringClass  $name       symlink name
	 * @return bool
	 * @author Dmitry (dio) Levashov
	 **/
	 protected function _symlink($source, $targetDir, $name) {
		return false;
	 }

	/**
	 * Copy file into another file (only inside one volume)
	 *
	 * @param  StringClass  $source  source file path
	 * @param  StringClass  $targetDir  target dir path
	 * @param  StringClass  $name    file name
	 * @return bool
	 * @author Dmitry (dio) Levashov
	 **/
	 protected function _copy($source, $targetDir, $name) {
		return false;
	 }
	
	/**
	 * Move file into another parent dir.
	 * Return new file path or false.
	 *
	 * @param  StringClass  $source  source file path
	 * @param  StringClass  $targetDir  target dir path
	 * @param  StringClass  $name    file name
	 * @return StringClass|bool
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _move($source, $targetDir, $name) {
		return false;
	}
	
	/**
	 * Remove file
	 *
	 * @param  StringClass  $path  file path
	 * @return bool
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _unlink($path) {
		
		$newkey = $this->_normpath($path);
		$newkey = preg_replace("/\/$/", "", $newkey);

		try {
			$obj = $this->s3->DeleteObject(array('Bucket' => $this->options['bucket'], 'Key' => $newkey));
		} catch (Exception $e) {
		
		}
		
		/*$fp = fopen('/tmp/eltest.txt','a+');
		
		fwrite($fp, 'key='.$newkey);*/
		
		if (is_object($obj)) {
			//fwrite($fp, 'obj='.var_export($obj,true));
			
			if (isset($obj->DeleteObjectResponse->Code)) {
				$rc = $obj->DeleteObjectResponse->Code;
				
				if (substr($rc, 0, 1) == '2') {
					return true;
				}
			}
		}

			
		//fclose($fp);
		
		return false;
	}

	/**
	 * Remove dir
	 *
	 * @param  StringClass  $path  dir path
	 * @return bool
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _rmdir($path) {
		return $this->_unlink($path . '/');
	}

	/**
	 * Create new file and write into it from file pointer.
	 * Return new file path or false on error.
	 *
	 * @param  resource $fp file pointer
	 * @param  StringClass $dir target dir path
	 * @param  StringClass $name file name
	 * @param  array $stat
	 * @return bool|StringClass
	 * @author Dmitry (dio) Levashov
	 */
	protected function _save($fp, $dir, $name, $stat) {
		return false;
	}
	
	/**
	 * Get file contents
	 *
	 * @param  StringClass  $path  file path
	 * @return StringClass|false
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _getContents($path) {
		return false;
	}
	
	/**
	 * Write a string to a file
	 *
	 * @param  StringClass  $path     file path
	 * @param  StringClass  $content  new file content
	 * @return bool
	 * @author Dmitry (dio) Levashov
	 **/
	protected function _filePutContents($path, $content) {
		return false;
	}

	/**
	 * Extract files from archive
	 *
	 * @param  StringClass  $path file path
	 * @param  array   $arc  archiver options
	 * @return bool
	 * @author Dmitry (dio) Levashov, 
	 * @author Alexey Sukhotin
	 **/
	 protected function _extract($path, $arc) {
		return false;
	 }

	/**
	 * Create archive and return its path
	 *
	 * @param  StringClass  $dir    target dir
	 * @param  array   $files  files names list
	 * @param  StringClass  $name   archive name
	 * @param  array   $arc    archiver options
	 * @return StringClass|bool
	 * @author Dmitry (dio) Levashov, 
	 * @author Alexey Sukhotin
	 **/
	protected function _archive($dir, $files, $name, $arc) {
		return false;
	}

	/**
	 * Detect available archivers
	 *
	 * @return void
	 * @author Dmitry (dio) Levashov, 
	 * @author Alexey Sukhotin
	 **/
	protected function _checkArchivers() {
		
	}

	/**
	 * chmod implementation
	 *
	 * @param StringClass $path
	 * @param StringClass $mode
	 * @return bool
	 */
	protected function _chmod($path, $mode) {
		return false;
	}

}

/**
 * SoapClient extension with Amazon S3 WSDL and request signing support
 *
 * @author Alexey Sukhotin
 **/
class S3SoapClient extends SoapClient {

	private $accesskey = ''; 
	private $secretkey = '';
	public $client = NULL;
	

	public function __construct($key = '', $secret = '') {
		$this->accesskey = $key;
		$this->secretkey = $secret;
		parent::__construct('http://s3.amazonaws.com/doc/2006-03-01/AmazonS3.wsdl');
	}


	/**
	 * Method call wrapper which adding S3 signature and default arguments to all S3 operations
	 *
	 * @param StringClass $method
	 * @param StringClass $arguments
	 * @return mixed
	 *
	 * @author Alexey Sukhotin
	 */
	public function __call($method, $arguments) {
		
		/* Getting list of S3 web service functions which requires signing */
		$funcs = $this->__getFunctions();
		
		$funcnames  = array();
		
		foreach ($funcs as $func) {
			preg_match("/\S+\s+([^\)]+)\(/", $func, $m);
	
			if (isset($m[1])) {
				$funcnames[] = $m[1];
			}
		}
		
		/* adding signature to arguments */
		if (in_array("{$method}", $funcnames)) {
		
			if (is_array($arguments[0])) {
				$arguments[0] = array_merge($arguments[0], $this->sign("{$method}"));
			} else {
				$arguments[0] = $this->sign("{$method}");
			}

		}
		
		/*$fp = fopen('/tmp/s3debug.txt', 'a+');
		fwrite($fp, 'method='."{$method}". ' timestamp='.date('Y-m-d H:i:s').' args='.var_export($arguments,true) . "\n");
		fclose($fp);*/
		return parent::__call($method, $arguments);
	}

	/**
	 * Generating signature and timestamp for specified S3 operation
	 *
	 * @param  StringClass  $operation    S3 operation name
	 * @return array
	 * @author Alexey Sukhotin
	 **/
	protected function sign($operation) {
	
		$params = array(
			'AWSAccessKeyId' => $this->accesskey,
			'Timestamp' => gmdate('Y-m-d\TH:i:s.000\Z'),
		);

		$sign_str = 'AmazonS3' . $operation . $params['Timestamp'];
		
		$params['Signature'] = base64_encode(hash_hmac('sha1', $sign_str, $this->secretkey, TRUE));
		
		return $params;
	}
	
}

