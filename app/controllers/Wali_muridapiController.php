<?php 
/**
 * Wali_murid Page Controller
 * @category  Controller
 */
class Wali_muridapiController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "wali_murid";
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
		$fields = array("wali_murid.id", 
			"wali_murid.nama", 
			"wali_murid.siswa_nis", 
			"siswa.nama AS siswa_nama", 
			"wali_murid.status", 
			"wali_murid.nomor_hp");
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); // get current pagination e.g array(page_number, page_limit)
		//search table record
		if(!empty($request->search)){
			$text = trim($request->search); 
			$search_condition = "(
				wali_murid.id LIKE ? OR 
				wali_murid.nama LIKE ? OR 
				wali_murid.siswa_nis LIKE ? OR 
				wali_murid.siswa_kelas LIKE ? OR 
				wali_murid.status LIKE ? OR 
				wali_murid.nomor_hp LIKE ?
			)";
			$search_params = array(
				"%$text%","%$text%","%$text%","%$text%","%$text%","%$text%"
			);
			//setting search conditions
			$db->where($search_condition, $search_params);
			 //template to use when ajax search
			$this->view->search_template = "wali_murid/search.php";
		}
		$db->join("siswa", "wali_murid.siswa_nis = siswa.nis", "INNER");
		if(!empty($request->orderby)){
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		}
		else{
			$db->orderBy("wali_murid.id", ORDER_TYPE);
		}
		if($fieldname){
			$db->where($fieldname , $fieldvalue); //filter by a single field name
		}
		$tc = $db->withTotalCount();
		$records = $db->get($tablename, $pagination, $fields);
		render_json($records);
	}
}
