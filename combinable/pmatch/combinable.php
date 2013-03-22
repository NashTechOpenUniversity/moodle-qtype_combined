<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines the hooks necessary to make the pmatch question type combinable
 *
 * @package    qtype
 * @subpackage combined
 * @copyright  2013 The Open University
 * @author     Jamie Pratt <me@jamiep.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/question/type/pmatch/pmatchlib.php');

class qtype_combined_combinable_type_pmatch extends qtype_combined_combinable_type_base {

    protected $identifier = 'pmatch';

    protected function extra_question_properties() {
        return array('forcelength' => '0', 'extenddictionary' => '', 'converttospace' => ',;:', 'synonymsdata' => array());
    }

    protected function extra_answer_properties() {
        return array('fraction' => '1', 'feedback' => array('text' => '', 'format' => FORMAT_PLAIN));
    }

    public function is_empty($subqformdata) {
        foreach (array('allowsubscript', 'allowsuperscript', 'usecase') as $field) {
            if (!empty($subqformdata->$field)) {
                return false;
            }
        }
        // Default of this one is on.
        if (empty($subqformdata->applydictionarycheck)) {
            return false;
        }
        if ('' !== trim($subqformdata->answer[0])) {
            return false;
        }
        return parent::is_empty($subqformdata);
    }
}


class qtype_combined_combinable_pmatch extends qtype_combined_combinable_accepts_width_specifier {

    /**
     * @return mixed
     */
    public function add_form_fragment(moodleform $combinedform, MoodleQuickForm $mform, $repeatenabled) {
        $susubels = array();
        $susubels[] = $mform->createElement('selectyesno', $this->field_name('allowsubscript'), get_string('allowsubscript',
            'qtype_pmatch'));
        $susubels[] = $mform->createElement('selectyesno', $this->field_name('allowsuperscript'), get_string('allowsuperscript', 'qtype_pmatch'));
        $mform->addGroup($susubels, $this->field_name('susubels'), get_string('allowsubscript', 'qtype_pmatch'),
                                                                    '&nbsp;'.get_string('allowsuperscript', 'qtype_pmatch'),
                                                                    false);
        $menu = array(
            get_string('caseno', 'qtype_pmatch'),
            get_string('caseyes', 'qtype_pmatch')
        );
        $casedictels = array();
        $casedictels[] = $mform->createElement('select', $this->field_name('usecase'), get_string('casesensitive',
            'qtype_pmatch'), $menu);
        $casedictels[] = $mform->createElement('selectyesno', $this->field_name('applydictionarycheck'),
                                                                            get_string('applydictionarycheck', 'qtype_pmatch'));
        $mform->addGroup($casedictels, $this->field_name('casedictels'), get_string('casesensitive', 'qtype_pmatch'),
                                                            '&nbsp;'.get_string('applydictionarycheck', 'qtype_pmatch'), false);
        $mform->setDefault($this->field_name('applydictionarycheck'), 1);
        $mform->addElement('textarea', $this->field_name('answer[0]'), get_string('answer', 'question'),
                                                             array('rows' => '6', 'cols' => '80', 'class' => 'textareamonospace'));
        $mform->setType($this->field_name('answer'), PARAM_RAW_TRIMMED);
    }

    public function data_to_form($context, $fileoptions) {
        return parent::data_to_form($context, $fileoptions);
    }


    public function validate() {
        $errors = array();
        $trimmedanswer = $this->formdata->answer[0];
        if ('' !== $trimmedanswer) {
            $expression = new pmatch_expression($trimmedanswer);
            if (!$expression->is_valid()) {
                $errors[$this->field_name('answer[0]')] = $expression->get_parse_error();
            }
        }
        return $errors;
    }
}