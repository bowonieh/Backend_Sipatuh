<?php 
/**
 * Siswa Page Controller
 * @category  Controller
 */
class SiswaapiController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "siswa";
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
		$fields = array("siswa.id", 
			"siswa.nis", 
			"siswa.nama", 
			"siswa.jenis_kelamin", 
			"siswa.kelas_id", 
			"kelas.nama AS kelas_nama", 
			"siswa.alamat");
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); // get current pagination e.g array(page_number, page_limit)
		//search table record
		if(!empty($request->search)){
			$text = trim($request->search); 
			$search_condition = "(
				siswa.id LIKE ? OR 
				siswa.nis LIKE ? OR 
				siswa.nama LIKE ? OR 
				siswa.jenis_kelamin LIKE ? OR 
				siswa.kelas_id LIKE ? OR 
				siswa.alamat LIKE ?
			)";
			$search_params = array(
				"%$text%","%$text%","%$text%","%$text%","%$text%","%$text%"
			);
			//setting search conditions
			$db->where($search_condition, $search_params);
			 //template to use when ajax search
			$this->view->search_template = "siswa/search.php";
		}
		$db->join("kelas", "siswa.kelas_id = kelas.id", "INNER");
		if(!empty($request->orderby)){
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		}
		else{
			$db->orderBy("siswa.id", ORDER_TYPE);
		}
		if($fieldname){
			$db->where($fieldname , $fieldvalue); //filter by a single field name
		}
		$tc = $db->withTotalCount();
		$records = $db->get($tablename, $pagination, $fields);
		render_json($records); //render the full page//render the full page
	}
	
	
	/**
     * Insert new record to the database table
	 * @param $formdata array() from $_POST
     * @return BaseView
     */
	function add($formdata = null){
		if($formdata){
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$request = $this->request;
			//fillable fields
			$fields = $this->fields = array("nis","nama","jenis_kelamin","kelas_id","alamat");
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'nis' => 'required',
				'nama' => 'required',
				'jenis_kelamin' => 'required',
				'kelas_id' => 'required',
				'alamat' => 'required',
			);
			$this->sanitize_array = array(
				'nis' => 'sanitize_string',
				'nama' => 'sanitize_string',
				'jenis_kelamin' => 'sanitize_string',
				'kelas_id' => 'sanitize_string',
				'alamat' => 'sanitize_string',
			);
			$this->filter_vals = true; //set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if($rec_id){
					$this->set_flash_msg("Record added successfully", "success");
					return	$this->redirect("siswa");
				}
				else{
					$this->set_page_error();
				}
			}
		}
		$page_title = $this->view->page_title = "Add New Siswa";
		$this->render_view("siswa/add.php");
	}
	/**
     * Update table record with formdata
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function edit($rec_id = null, $formdata = null){
		$request = $this->request;
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		 //editable fields
		$fields = $this->fields = array("id","nis","nama","jenis_kelamin","kelas_id","alamat");
		if($formdata){
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'nis' => 'required',
				'nama' => 'required',
				'jenis_kelamin' => 'required',
				'kelas_id' => 'required',
				'alamat' => 'required',
			);
			$this->sanitize_array = array(
				'nis' => 'sanitize_string',
				'nama' => 'sanitize_string',
				'jenis_kelamin' => 'sanitize_string',
				'kelas_id' => 'sanitize_string',
				'alamat' => 'sanitize_string',
			);
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("siswa.id", $rec_id);;
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount(); //number of affected rows. 0 = no record field updated
				if($bool && $numRows){
					$this->set_flash_msg("Record updated successfully", "success");
					return $this->redirect("siswa");
				}
				else{
					if($db->getLastError()){
						$this->set_page_error();
					}
					elseif(!$numRows){
						//not an error, but no record was updated
						$page_error = "No record updated";
						$this->set_page_error($page_error);
						$this->set_flash_msg($page_error, "warning");
						return	$this->redirect("siswa");
					}
				}
			}
		}
		$db->where("siswa.id", $rec_id);;
		$data = $db->getOne($tablename, $fields);
		$page_title = $this->view->page_title = "Edit  Siswa";
		if(!$data){
			$this->set_page_error();
		}
		return $this->render_view("siswa/edit.php", $data);
	}
	/**
     * Update single field
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function editfield($rec_id = null, $formdata = null){
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		//editable fields
		$fields = $this->fields = array("id","nis","nama","jenis_kelamin","kelas_id","alamat");
		$page_error = null;
		if($formdata){
			$postdata = array();
			$fieldname = $formdata['name'];
			$fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array(
				'nis' => 'required',
				'nama' => 'required',
				'jenis_kelamin' => 'required',
				'kelas_id' => 'required',
				'alamat' => 'required',
			);
			$this->sanitize_array = array(
				'nis' => 'sanitize_string',
				'nama' => 'sanitize_string',
				'jenis_kelamin' => 'sanitize_string',
				'kelas_id' => 'sanitize_string',
				'alamat' => 'sanitize_string',
			);
			$this->filter_rules = true; //filter validation rules by excluding fields not in the formdata
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("siswa.id", $rec_id);;
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount();
				if($bool && $numRows){
					return render_json(
						array(
							'num_rows' =>$numRows,
							'rec_id' =>$rec_id,
						)
					);
				}
				else{
					if($db->getLastError()){
						$page_error = $db->getLastError();
					}
					elseif(!$numRows){
						$page_error = "No record updated";
					}
					render_error($page_error);
				}
			}
			else{
				render_error($this->view->page_error);
			}
		}
		return null;
	}
	/**
     * Delete record from the database
	 * Support multi delete by separating record id by comma.
     * @return BaseView
     */
	function delete($rec_id = null){
		Csrf::cross_check();
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$this->rec_id = $rec_id;
		//form multiple delete, split record id separated by comma into array
		$arr_rec_id = array_map('trim', explode(",", $rec_id));
		$db->where("siswa.id", $arr_rec_id, "in");
		$bool = $db->delete($tablename);
		if($bool){
			$this->set_flash_msg("Record deleted successfully", "success");
		}
		elseif($db->getLastError()){
			$page_error = $db->getLastError();
			$this->set_flash_msg($page_error, "danger");
		}
		return	$this->redirect("siswa");
	}
}
