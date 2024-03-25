<?php 
/**
 * Kelas Page Controller
 * @category  Controller
 */
class KelasapiController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "kelas";
	}
	/**
     * List page records
     * @param $fieldname (filter record by a field) 
     * @param $fieldvalue (filter field value)
     * @return BaseView
     */
	function index($fieldname = null , $fieldvalue = null){
		$request = (object) $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = array("kelas.id", 
			"kelas.nama", 
			"kelas.tingkat_id", 
			"tingkat.tingkat AS tingkat_tingkat", 
			"kelas.jurusan_id", 
			"jurusan.nama AS jurusan_nama");
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); // get current pagination e.g array(page_number, page_limit)
		//search table record
		if(!empty($request->search)){
			$text = trim($request->search); 
			$search_condition = "(
				kelas.id LIKE ? OR 
				kelas.nama LIKE ? OR 
				kelas.tingkat_id LIKE ? OR 
				kelas.jurusan_id LIKE ?
			)";
			$search_params = array(
				"%$text%","%$text%","%$text%","%$text%"
			);
			//setting search conditions
			$db->where($search_condition, $search_params);
			 //template to use when ajax search
			$this->view->search_template = "kelas/search.php";
		}
		$db->join("tingkat", "kelas.tingkat_id = tingkat.id", "INNER");
		$db->join("jurusan", "kelas.jurusan_id = jurusan.id", "INNER");
		if(!empty($request->orderby)){
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		}
		else{
			$db->orderBy("kelas.id", ORDER_TYPE);
		}
		if($fieldname){
			$db->where($fieldname , $fieldvalue); //filter by a single field name
		}
		$tc = $db->withTotalCount();
		$records = $db->get($tablename, '', $fields);
		render_json($records);
	}
	
	
}
