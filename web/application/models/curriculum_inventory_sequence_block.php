<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once "ilios_base_model.php";

/**
 * Data Access Object (DAO) for the "curriculum_inventory_sequence_block" table.
 */
class Curriculum_Inventory_Sequence_Block extends Ilios_Base_Model
{
    /**
     * @var int
     */
    const REQUIRED = 1;
    /**
     * @var int
     */
    const OPTIONAL = 2;
    /**
     * @var int
     */
    const REQUIRED_IN_TRACK = 3;

    /**
     * @var int
     */
    const ORDERED = 1;

    /**
     * @var int
     */
    const UNORDERED = 2;

    /**
     * @var int
     */
    const PARALLEL = 3;

    /**
     * Constructor.
     */
    public function __construct ()
    {
        parent::__construct('curriculum_inventory_sequence_block', array('curriculum_inventory_sequence_block_id'));
    }

    /**
     * Retrieves the sequence blocks associated with a given program year.
     * @param int $programYearId the program year id.
     * @return array a nested array of arrays, each sub-array is representing a sequence block record
     */
    public function getBlocks ($programYearId)
    {
        $rhett = array();
        $clean = array();
        $clean['py_id'] = (int) $programYearId;
        $sql =<<< EOL
SELECT sb.*, al.level AS 'academic_level_number',
c.clerkship_type_id AS 'course_clerkship_type_id'
FROM {$this->databaseTableName} sb
JOIN curriculum_inventory_academic_level al ON al.academic_level_id = sb.academic_level_id
LEFT JOIN course c ON c.course_id = sb.course_id
WHERE sb.program_year_id = {$clean['py_id']}
EOL;

        $query = $this->db->query($sql);
        if (0 < $query->num_rows()) {
            foreach ($query->result_array() as $row) {
                $rhett[] = $row;
            }
        }
        $query->free_result();
        return $rhett;
    }
}