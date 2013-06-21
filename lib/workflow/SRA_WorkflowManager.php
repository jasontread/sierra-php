<?php
// {{{ Header
/*
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 | SIERRA : PHP Application Framework  http://code.google.com/p/sierra-php |
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 | Copyright 2005 Jason Read                                               |
 |                                                                         |
 | Licensed under the Apache License, Version 2.0 (the "License");         |
 | you may not use this file except in compliance with the License.        |
 | You may obtain a copy of the License at                                 |
 |                                                                         |
 |     http://www.apache.org/licenses/LICENSE-2.0                          |
 |                                                                         |
 | Unless required by applicable law or agreed to in writing, software     |
 | distributed under the License is distributed on an "AS IS" BASIS,       |
 | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.|
 | See the License for the specific language governing permissions and     |
 | limitations under the License.                                          |
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 */
// }}}

// {{{ Imports
require_once('workflow/SRA_Workflow.php');
// }}}

// {{{ Constants

// }}}

// {{{ SRA_WorkflowManager
/**
 * facade used to interract with the workflow package. all methods are static. 
 * see api for more info. In order to utilize a SIERRA::workflow within your 
 * application, your app-config must include the "lib/workflow/workflow.xml" 
 * entity model 
 * (<use-entity-model key="workflow" path="lib/workflow/workflow.xml" />)
 * 
 * @author  Jason Read <jason@idir.org>
 * @package sierra.workflow
 */
class SRA_WorkflowManager {
  
  // {{{ Operations
	
	
	// Static methods
  
	// {{{ initializeWorkflow
	/**
	 * Used to initialize a new workflow instance based on the parameters 
   * specified. returns the new workflow instance. invoke the "start()" method 
   * to advance to the first step. use the "workflowId" parameter to resume 
   * this workflow in the future using the SRA_WorkflowManager::resumeWorkflow 
   * method
   * 
	 * @param  string $id the "use-workflow" identifier from app-config
   * @param array $params optional initialization parameters. this is an 
   * associative array of key/value pairs used by the workflow front-end 
   * implementation and not within SIERRA::workflow. if duplicate parameters 
   * exist between these and the descriptor, the descriptor params will be 
   * overwritten
	 * @access	public
	 * @return	SraWorkflowVO
	 */
	function &initializeWorkflow($id, $params=NULL) {
    if (SRA_Workflow::isValid($setup =& SRA_WorkflowManager::getWorkflowSetup($id))) {
      $dao =& SRA_DaoFactory::getDao('SraWorkflow');
      $workflow =& $dao->newInstance(array('id' => $id));
      if ($setup->params)  { $workflow->appendParams($setup->params); }
      if ($params) { $workflow->appendParams($params); }
      return SRA_Error::isError($err = $dao->insert($workflow)) ? $err : $workflow;
    }
    else {
      return $setup;
    }
	}
	// }}}
  
	// {{{ getWorkflowSetup
	/**
	 * return the SRA_Workflow instance representing the xml workflow descriptor 
   * specified. returns an SRA_Error object if there are problems with the 
   * descriptor
   * 
	 * @param  string $id the "use-workflow" identifier from app-config
	 * @access	public
	 * @return	SRA_Workflow
	 */
	function &getWorkflowSetup($id) {
    static $_sraWorkflows = array();
    if (!isset($_sraWorkflows[$id]) && ($wf = SRA_Controller::getAppWorkflow($id))) {
      $conf = file_exists($wf['path']) ? $wf['path'] : SRA_File::getRelativePath(FALSE, $wf['path'], basename(SRA_Controller::getAppConfDir()));
      if (!file_exists($conf) && !SRA_Util::endsWith($conf, '.xml')) { $conf = file_exists($wf['path'] . '.xml') ? $wf['path'] . '.xml' : SRA_File::getRelativePath(FALSE, $wf['path'] . '.xml', basename(SRA_Controller::getAppConfDir())); }
      if (file_exists($conf) && SRA_XmlParser::isValid($parser =& SRA_XmlParser::getXmlParser($conf, TRUE))) {
        $_sraWorkflows[$id] = SRA_Workflow::isValid($workflow = new SRA_Workflow($parser->getData('workflow'))) ? $workflow : SRA_Error::logError("SRA_WorkflowManager::getWorkflow: Failed - Workflow descriptor ${conf} produced errors", __FILE__, __LINE__);
      }
      else {
        $_sraWorkflows[$id] = SRA_Error::logError("SRA_WorkflowManager::getWorkflow: Failed - Workflow descriptor ${base} is not valid", __FILE__, __LINE__);
      }
    }
    return $_sraWorkflows[$id];
	}
	// }}}
	
	// {{{ resumeWorkflow
	/**
	 * Used to resume a workflow previously created through the 
   * SRA_WorkflowManager::initializeWorkflow method
   * 
	 * @param  int $id the "workflowId" unique identifier for the workflow 
   * instance
	 * @access	public
	 * @return	SraWorkflowVO
	 */
	function &resumeWorkflow($id) {
    if (SRA_Error::isError($dao =& SRA_DaoFactory::getDao('SraWorkflow'))) {
      return $dao;
    }
    else {
      return $dao->findByPk($id);
    }
	}
	// }}}
  
  // private operations
  
}
// }}}
?>
