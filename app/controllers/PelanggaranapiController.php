<?php 
/**
 * Pelanggaran Page Controller
 * @category  Controller
 */
class PelanggaranapiController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "pelanggaran";
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
		$fields = array("pelanggaran.id", 
			"pelanggaran.nis", 
			"pelanggaran.nama", 
			"pelanggaran.kelas", 
			"pelanggaran.tanggal", 
			"pelanggaran.jenis_id", 
			"jenis_pelanggaran.nama AS jenis_pelanggaran_nama", 
			"pelanggaran.detail");
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); // get current pagination e.g array(page_number, page_limit)
		//search table record
		if(!empty($request->search)){
			$text = trim($request->search); 
			$search_condition = "(
				pelanggaran.id LIKE ? OR 
				pelanggaran.nis LIKE ? OR 
				pelanggaran.nama LIKE ? OR 
				pelanggaran.kelas LIKE ? OR 
				pelanggaran.tanggal LIKE ? OR 
				pelanggaran.jenis_id LIKE ? OR 
				pelanggaran.detail LIKE ?
			)";
			$search_params = array(
				"%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%"
			);
			//setting search conditions
			$db->where($search_condition, $search_params);
			 //template to use when ajax search
			$this->view->search_template = "pelanggaran/search.php";
		}
		$db->join("jenis_pelanggaran", "pelanggaran.jenis_id = jenis_pelanggaran.id", "INNER");
		if(!empty($request->orderby)){
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		}
		else{
			$db->orderBy("pelanggaran.id", ORDER_TYPE);
		}
		if($fieldname){
			$db->where($fieldname , $fieldvalue); //filter by a single field name
		}
		$tc = $db->withTotalCount();
		$records = $db->get($tablename, $pagination, $fields);
		render_json($records); //render the full page
	}
	
	/**
     * Insert new record to the database table
	 * @param $formdata array() from $_POST
     * @return BaseView
     */
	function add($formdata = null){
		$responseError = array(
			"message" => "Tambah data kota terlebih dahulu"
		);
		if($formdata){
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$request = $this->request;
			//fillable fields
			$fields = $this->fields = array("nis","nama","kelas","tanggal","jenis_id","detail");
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'nis' => 'required',
				'nama' => 'required',
				'kelas' => 'required',
				'tanggal' => 'required',
				'jenis_id' => 'required',
				'detail' => 'required',
			);
			$this->sanitize_array = array(
				'nis' => 'sanitize_string',
				'nama' => 'sanitize_string',
				'kelas' => 'sanitize_string',
				'tanggal' => 'sanitize_string',
				'jenis_id' => 'sanitize_string',
				'detail' => 'sanitize_string',
			);
			$this->filter_vals = true; //set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if($rec_id){
					return render_json(
						array(
							'message' => 'Record added succesfully',
							'rec_id' => $rec_id,
							'table_name' => $tablename,
							'model_data' => $modeldata,
						)
					);
				}
				else{
					$this->set_page_error();
				}
			}
		}
		echo render_json($responseError);
	}
	
}
