<?php
namespace Ezrun\Core;

class Upload  extends BaseCore {
    
	private $db = null;
	private $main = null;

	public function __construct($db, $main) {
		$this->db = $db;
		$this->main = $main;
	}
	
	public function uploadImage($fieldname, $sub_folder = false) {
		
		if(isset($_FILES[$fieldname]['name']) && !empty($_FILES[$fieldname]['name'])) {
			
			$rel_folder = ($sub_folder ? $sub_folder . '/' : '');
			$date_folder = date('Y') . '/' . date('m') . '/' . date('d') . '/';
			$folder = UPLOAD_PICS_PATH . $rel_folder . $date_folder;
			
			//create folder
			if(!is_dir($folder)) mkdir( $folder, 0777, true );
			
			$file = $this->doUpload($_FILES[$fieldname], UPLOAD_PICS_PATH, $rel_folder . $date_folder);
			
			if($file) {
				
				$fileinfo = $this->getFileMetadata($file, UPLOAD_PICS_PATH, $rel_folder . $date_folder);
				
				return array('name' => $_FILES[$fieldname]['name'], 'file' => $rel_folder . $date_folder . $file, 'info' => $fileinfo);
			}
			
		}
		return false;
	}
	
	public function moveImage($filename, $subpath = false) {
		
		$rel_folder = ($subpath ? $subpath . '/' : '');
		$date_folder = date('Y') . '/' . date('m') . '/' . date('d') . '/';
		$folder = UPLOAD_PICS_PATH . $rel_folder . $date_folder;
		
		//create folder
		if(!is_dir($folder)) mkdir( $folder, 0777, true );
		
		//get extension
		$ext = $this->getExtension($filename);
		$name = $this->generateFilename($filename) . '.' . $ext;
		
		rename(TEMPORARY_UPLOAD_FOLDER . $filename, $folder . $name);
		
		$fileinfo = $this->getFileMetadata($name, UPLOAD_PICS_PATH, $rel_folder . $date_folder);
		
		return array('name' => $filename, 'file' => $rel_folder . $date_folder . $name, 'info' => $fileinfo);
	}
	
	public function deleteImage($file, $sub_folder = false) {
		
		return $this->remove($file, UPLOAD_PICS_PATH, $sub_folder);
	}
	
	private function doUpload($file, $main_folder, $sub_folder) {
		
		//upload file
		$uploadFile = $main_folder . $sub_folder . $file['name'];
		if(move_uploaded_file($file['tmp_name'], $uploadFile)) {
			
			//get extension
			$ext = $this->getExtension($file['name']);
			
			$name = $this->generateFilename($file['name']) . '.' . $ext;
			$fileReady = $main_folder . $sub_folder . $name;
			rename($uploadFile, $fileReady);
			
			return $name;
		}
		return false;
	}
	
	public function remove($file, $main_folder, $sub_folder = false) {
		
		$remove = $main_folder . ($sub_folder ? $sub_folder . DS : '') . $file;
		if(is_file($remove)) return unlink($remove);
		else return false;
	}
	
	private function generateFilename($name) {
		
		sleep(1);
		
		$timestamp = time();
		$filename = md5($name . $timestamp);
		
		return $filename;
	}
	
	private function getExtension($file) {
		
		$ext_array = explode('.', $file);
		$ext = strtolower(array_pop($ext_array));
		
		return $ext;
	}
	
	public function getFileMetadata($file, $main_folder, $sub_folder) {
		
		$filepath = $main_folder . $sub_folder . $file;
		
		$getID3 = new getID3;
		$fileinfo = $getID3->analyze($filepath);
		
		return $fileinfo;
	}
}
