<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * @package ADP_Test_Plugin
 * @subpackage ADP_Test_Plugi/includes/admin
 */

( defined( 'WPINC' ) && current_user_can( 'manage_options' ) ) || die;
?>

<script type="text/javascript">

    function <?= $this->classname_lc ?>DrugsOptions() {


        function deleteElement(element) {
            element.parentNode.removeChild(element);
        }

        function addElement(element) {
            let html = '<?= str_replace('\'', '\\\'', $this->drug_field()) ?>';

            let label_for = '<?= $this->classname_lc ?>_field_list_drugs';
            let id_input = label_for + '_new_' + (new Date().getTime());
            let id_container = 'container_' + id_input;
            let data_cutom = 'custom';
            let name = '<?= $this->classname_lc ?>_options[' + label_for + '][]';
            html = html.replace(/%1\$./g, id_input).replace(/%2\$./g, data_cutom).replace(/%3\$./g, name).replace(/%4\$./g, "").replace(/%5\$./g, id_container).replace(/%6\$./g, '<?= $this->classname_lc ?>');

            let template = document.createElement('template');
            html = html.trim(); // Never return a text node of whitespace as the result
            template.innerHTML = html;
            let newElements = template.content.firstChild;

            //element.append(newElements);
            element.parentNode.insertBefore(newElements, element);
        }

        return Object.freeze({
            deleteElement,
            addElement
        });
    }

</script>
