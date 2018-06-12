<?php
use Framework\AbstractController;
use Framework\DataTable\DataTable;

require_once(__DIR__."/../../../library/pid.inc");
require_once(__DIR__."/../../../library/formatting.inc.php");
require_once(__DIR__."/../../../library/patient.inc");

class PatientsController extends AbstractController
{
    protected $entry = null;

    public function getTitle()
    {
        return xl("Patients Tags");
    }


    public function buildDataTable()
    {
        $entry = new PatientTagEntry();
        $this->entry = $entry;
        $dataTable = new DataTable(
            $entry,
            'patients-tags-table',
            $this->getBaseUrl()."/index.php?action=patients!results",
            null,
            $this->getBaseUrl()."/index.php?action=patients!setpid" );
        return $dataTable;
    }

    public function _action_view_tags()
    {
        $pid = $this->request->getParam('pid');
        $repo = new PatientTagRepository();
        $this->view->tags = $repo->fetchTagsForPatient( $pid );
        $this->setViewScript( 'views/patients_tags.php' );
    }

    public function _action_save_tags()
    {
        $pid = $this->request->getParam('pid');
        $tagsToAdd = $this->request->getParam('tags');
        $repo = new PatientTagRepository();
        $currentTags = $repo->fetchTagsForPatient( $pid );
        $tagsToDelete = array();
        if ( !is_array( $tagsToAdd ) ) {
            $tagsToAdd = array();
        }

        foreach ( $currentTags as $ct ) {
            if ( ( $key = array_search( $ct->tag_id, $tagsToAdd ) ) !== false ) {
                // We already have this tag, so don't add
                unset( $tagsToAdd[$key] );
            } else if ( array_search( $ct->tag_id, $tagsToAdd ) === false ) {
                // We had this tag, but it's not in our new list, so delete it
                $tagsToDelete[]= $ct->tag_id;
            }
        }
        $repo->deleteTagsForPatient( $tagsToDelete, $pid );
        $repo->addTagsForPatient( $tagsToAdd, $pid );
        $this->view->tags = $repo->fetchTagsForPatient( $pid );
        $this->setViewScript( 'views/patients_tags.php' );
    }

    /*
     * Display edit view for editing a patient's tags
     */
    public function _action_edit()
    {
        $pid = $this->request->getParam('pid');
        $repo = new PatientTagRepository();
        $this->view->tags = $repo->fetchTagsForPatient( $pid );
        $tagRepo = new TagRepository();
        $this->view->tagColors = $tagRepo->getColorOptions();
        $this->view->tagsJson = json_encode( $tagRepo->fetchAll() );
        $this->setViewScript( 'forms/patients_tags_form.php' );
    }

    public function _action_index()
    {
        $this->view->dataTable = $this->buildDataTable();
        $this->view->title = $this->getTitle();
        $this->view->navbar = __DIR__."/../views/navbars/patients_tags.php";
        $this->view->modal = "";
        $this->setViewScript( 'list.php', 'layouts/patients_tags_layout.php' );
    }

    public function _action_results()
    {
        $dataTable = $this->buildDataTable();
        echo $dataTable->getResults( $this->getRequest() );
    }

    public function _action_details()
    {
        $encounterId = $this->request->getParam('id');
        $pid = $this->request->getParam('pid');
        $this->view->encounter = $encounterId;
        $this->view->pid = $pid;
        $this->setViewScript('details/patients_tags.php');
    }

    public function _action_setpid()
    {
        // All functionality for setting active patient and encounter via ajax
        $pid = $this->request->getParam( 'pid' );
        $encounter = $this->request->getParam( 'encounter' );

        setpid($pid);
        $sql = "SELECT CONCAT( fname, ' ', lname ) AS patient_name, pubpid, pid, DATE_FORMAT( DOB,'%Y-%m-%d' ) as DOB_YMD
           FROM patient_data
           WHERE pid = ?
           LIMIT 1";
        $patientRow = sqlQuery( $sql, array( $pid ) );

        $patientName = $patientRow['patient_name'];
        $pubpid = $patientRow['pubpid'];
        $str_dob = xl('DOB') . ": " . oeFormatShortDate( $patientRow['DOB_YMD'] ) . " " . xl('Age') . ": " . getPatientAge( $patientRow['DOB_YMD'] );

        // Find all encounters for this patient and sort by date
        $sql = "SELECT fe.encounter, fe.date, opc.pc_catname
          FROM form_encounter AS fe
          LEFT JOIN openemr_postcalendar_categories opc on fe.pc_catid = opc.pc_catid
          WHERE fe.pid = ? order by fe.date desc";
        $result = sqlStatement( $sql, array( $pid ) );

        $encounterIdArray = array();
        $encounterDateArray = array();
        $encounterCategoryArray = array();
        while ( $row = sqlFetchArray( $result ) ) {
            $encounterIdArray []= $row['encounter'];
            $encounterDateArray []= oeFormatShortDate( date( "Y-m-d", strtotime( $row['date'] ) ) );
            $encounterCategoryArray []= $row['pc_catname'];
        }

        // Build an array of encounter data and use json_encode to conver php
        // array to json
        $data = array(
            'patientname' => $patientName,
            'pid' => $pid,
            'pubpid' => $pubpid,
            'frname' => 'RTop',
            'str_dob' => $str_dob,
            'encounter' => $encounter,
            'encounterIdArray' => $encounterIdArray,
            'encounterDateArray' => $encounterDateArray,
            'calendarCategoryArray' => $encounterCategoryArray,

        );
        echo json_encode($data);
    }
}