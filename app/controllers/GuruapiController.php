<?php 
/**
 * Guru Page Controller
 * @category  Controller
 */
class GuruApiController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "guru";
	}
	function apilist(){
		$request = (object) $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = array(
			"guru.id",
			"guru.nip",
			"guru.nama",
			"guru.jabatan",
			"guru.kelas_id",
			"guru.nomor_hp"
		);
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); 
		if (!empty($request->search)) {
			$text = trim($request->search);
			$search_condition = "(
				guru.id LIKE ? OR 
				guru.nip LIKE ? OR 
				guru.nama LIKE ? OR 
				guru.jabatan LIKE ? OR 
				guru.kelas_id LIKE ? OR 
				guru.nomor_hp LIKE ?
			)";
			$search_params = array(
				"%$text%", "%$text%", "%$text%", "%$text%", "%$text%", "%$text%"
			);
			//setting search conditions
			$db->where($search_condition, $search_params);
			//template to use when ajax search
		}

		$records = $db->get($tablename, '', $fields);

		if ($db->getLastError()) {
			$this->set_page_error();
		}
		echo render_json($records);
	}
}
