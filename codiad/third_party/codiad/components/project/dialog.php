<?php

/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See 
 *  [root]/license.txt for more. This information must remain intact.
*/


require_once('../../common.php');

//////////////////////////////////////////////////////////////////
// Verify Session or Key
//////////////////////////////////////////////////////////////////

checkSession();

switch($_GET['action']){

  //////////////////////////////////////////////////////////////
  // List Projects Mini Sidebar
  //////////////////////////////////////////////////////////////
  case 'sidelist':

  // Get access control data
  $projects_assigned = false;
  if(file_exists(BASE_PATH . "/data/" . $_SESSION['user'] . '_acl.php')){
    $projects_assigned = getJSON($_SESSION['user'] . '_acl.php');
  }

?>

<ul>

  <?php

  // Get projects JSON data
  $projects = getJSON('projects.php');
  sort($projects);
  foreach($projects as $project=>$data){
    $show = true;
    if($projects_assigned && !in_array($data['path'],$projects_assigned)){ $show=false; }
    if($show){
      if($_GET['trigger'] == 'true') {
  ?>
  <li onclick="codiad.project.open('<?php echo($data['path']); ?>');"><div class="icon-archive icon"></div><?php echo($data['name']); ?></li>

  <?php
      } else {
  ?>
  <li ondblclick="codiad.project.open('<?php echo($data['path']); ?>');"><div class="icon-archive icon"></div><?php echo($data['name']); ?></li>

  <?php
      }
    }
  } 
  ?>

</ul>

<?php

  break;

  //////////////////////////////////////////////////////////////
  // List Projects
  //////////////////////////////////////////////////////////////

  case 'list':

  // Get access control data
  $projects_assigned = false;
  if(file_exists(BASE_PATH . "/data/" . $_SESSION['user'] . '_acl.php')){
    $projects_assigned = getJSON($_SESSION['user'] . '_acl.php');
  }

?>
<label><?php i18n("Project List"); ?></label>
<div id="project-list">
  <table class="codiad" width="100%">
    <tr>
      <th width="70"><?php i18n("Open"); ?></th>
      <th width="150"><?php i18n("Project Name"); ?></th>
      <th width="250"><?php i18n("Path"); ?></th>
      <?php if(checkAccess()){ ?><th width="70"><?php i18n("Delete"); ?></th><?php } ?>
    </tr>
  </table>
  <div class="project-wrapper">
    <table class="codiad" width="100%" style="word-wrap: break-word;word-break: break-all;">
      <?php

  // Get projects JSON data
  $projects = getJSON('projects.php');
  sort($projects);
  foreach($projects as $project=>$data){
    $show = true;
    if($projects_assigned && !in_array($data['path'],$projects_assigned)){ $show=false; }
    if($show) {
      ?>
      <tr <?php if($_SESSION['project'] == $data['path']) { ?> class="active-project-row" <?php } ?> >
        <td width="70"><a onclick="codiad.project.open('<?php echo($data['path']); ?>');" class="icon-folder bigger-icon"></a></td>
        <td width="150"><?php echo($data['name']); ?></td>
        <td width="250"><?php echo($data['path']); ?></td>
        <?php
      if(checkAccess()){
        if($_SESSION['project'] == $data['path']){
        ?>
        <td width="70"><a onclick="codiad.message.error(i18n('Active Project Cannot Be Removed'));" class="icon-block bigger-icon"></a></td>
        <?php
        }else{
        ?>
        <td width="70"><a onclick="codiad.project.delete('<?php echo($data['name']); ?>','<?php echo($data['path']); ?>');" class="icon-cancel-circled bigger-icon"></a></td>
        <?php
        }
      }
        ?>
      </tr>
      <?php
    }
  }
      ?>
    </table>
  </div>
</div>
<?php if(checkAccess()){ ?>
<button class="codiad " onclick="codiad.project.create();"><?php i18n("New Project"); ?></button>
<?php } ?>
<button class="codiad" onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
<?php

  break;

  //////////////////////////////////////////////////////////////////////
  // Create New Project
  //////////////////////////////////////////////////////////////////////

  case 'create':
  $from_git_repo = false;
  if (isset($_GET['from_git_repo'])) {
    $from_git_repo = true;
  }
?>
<form class="codiad-form">
  <label><?php i18n("Project Name"); ?></label>
  <input name="project_name" autofocus="autofocus" autocomplete="off"
         value="<?php if (isset($_GET['git_repo_name'])) { echo $_GET['git_repo_name']; } ?>">
  <label><?php i18n("Folder Name or Path"); ?></label>
  <input name="project_path" autofocus="off" autocomplete="off"
         placeholder="Leave blank to use project name">

  <!-- Clone From GitHub -->
  <div style="width: 500px;">
    <table class="codiad <?php if (!$from_git_repo) { echo 'hide'; } ?>" id="git-clone">
      <tr>
        <td>
          <label><?php i18n("Git Repository"); ?></label>
          <input name="git_repo"
                 value="<?php if ($from_git_repo) { echo $_GET['from_git_repo']; } ?>"
                 <?php if ($from_git_repo) { echo 'readonly'; } ?>>
        </td>
        <td width="5%">&nbsp;</td>
        <td width="25%">
          <label><?php i18n("Branch"); ?></label>
          <input name="git_branch" value="master">
        </td>
      </tr>
      <tr>
        <td colspan="3" class="note">Note: This will only work if your Git repo DOES NOT require
          interactive authentication and your server has git installed.
        </td>
      </tr>
    </table>
  </div>
  <!-- /Clone From GitHub -->
  <?php
  $action = 'codiad.project.list();';
  if($_GET['close'] == 'true') {
    $action = 'codiad.modal.unload();';
  }
  ?>
  <button class="codiad "><?php i18n("Create"); ?></button>
  <?php if (!$from_git_repo) { ?>
  <button class="codiad" onclick="$('#git-clone').slideDown(300); $(this).hide(); return false;">
    <?php i18n("...From Git Repo"); ?></button>
  <?php } ?>
  <button class="codiad " onclick="<?php echo $action;?>return false;"><?php i18n("Cancel"); ?></button>
  <img src="themes/default/loading.gif" id="loading-img" style="display: none;"/>
</form>
<?php
  break;

  //////////////////////////////////////////////////////////////////
  // Rename
  //////////////////////////////////////////////////////////////////
  case 'rename':
?>
<form class="codiad-form">
  <input type="hidden" name="project_path" value="<?php echo($_GET['path']); ?>">
  <label><span class="icon-pencil"></span><?php i18n("Rename Project"); ?></label>    
  <input type="text" name="project_name" autofocus="autofocus" autocomplete="off" value="<?php echo($_GET['name']); ?>">  
  <button class="codiad "><?php i18n("Rename"); ?></button>&nbsp;<button class="codiad " onclick="codiad.modal.unload(); return false;"><?php i18n("Cancel"); ?></button>
</form>
<?php
  break;

  //////////////////////////////////////////////////////////////////////
  // Delete Project
  //////////////////////////////////////////////////////////////////////

  case 'delete':

?>
<form class="codiad-form">
  <input type="hidden" name="project_path" value="<?php echo($_GET['path']); ?>">
  <label><?php i18n("Confirm Project Deletion"); ?></label>
  <pre class="codiad"><?php i18n("Name:"); ?> <?php echo($_GET['name']); ?>, <?php i18n("Path:") ?> <?php echo($_GET['path']); ?></pre>
  <table class="codiad">
    <tr><td width="5"><input type="checkbox" name="delete" id="delete" value="true"></td><td><?php i18n("Delete Project Files"); ?></td></tr>
    <tr><td width="5"><input type="checkbox" name="follow" id="follow" value="true"></td><td><?php i18n("Follow Symbolic Links "); ?></td></tr>
  </table>
  <button class="codiad "><?php i18n("Confirm"); ?></button><button class="codiad " onclick="codiad.project.list();return false;"><?php i18n("Cancel"); ?></button>
  <?php
  break;

}

  ?>

