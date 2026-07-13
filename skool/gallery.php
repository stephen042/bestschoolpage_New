<?php
include('../config.php');
include('inc.session-create.php');

$PageTitle = "Gallery";
$FileName = 'gallery.php';

// ============================================================================
// FIX: USE CORRECT USER IDENTIFICATION (Same as dashboard.php)
// ============================================================================
// Use the same method as class_teacher_roll_call_bulk.php
$create_by_userid = (int)($_SESSION['userid'] ?? 0);

// If create_by_userid is not set in session, try to get it from the user record
if ($create_by_userid == 0 && !empty($_SESSION['userid'])) {
	$userData = db_get_row("SELECT create_by_userid FROM users WHERE id = ?", [$_SESSION['userid']]);
	if ($userData && !empty($userData['create_by_userid'])) {
		$create_by_userid = (int)$userData['create_by_userid'];
	}
}

// Fallback: if still 0, use the user's own ID
if ($create_by_userid == 0) {
	$create_by_userid = (int)($_SESSION['userid'] ?? 0);
}

// Also get the usertype correctly
$create_by_usertype = $_SESSION['usertype'] ?? '';

// ============================================================================
// INITIALIZATION
// ============================================================================
$validate = new Validation();
$stat = [];

// Get action safely with null coalescing
$action = $_GET['action'] ?? '';
$randomid = $_GET['randomid'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}

// ============================================================================
// ADD NEW RECORD
// ============================================================================
if (isset($_POST['submit'])) {
	if ($validate->validate() && count($stat) == 0) {
		$newfile = '';

		if (isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"])) {
			$filename = basename($_FILES['image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.') + 1));
			if (in_array($ext1, array('jpg', 'png', 'gif', 'jpeg'))) {
				$newfile = md5(time()) . "_" . $filename;
				move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $newfile);
			}
		}

		$iLastId = db_get_val("SELECT id FROM school_gallery ORDER BY id DESC") + 1;
		$randomId = randomFix(15) . '-' . $iLastId;

		$aryData = array(
			'usertype'              =>  $_SESSION['usertype'] ?? '',
			'userid'                =>  $_SESSION['userid'] ?? 0,
			'image'                 =>  $newfile,
			'status'                =>  $_POST['status'],
			'create_by_userid'      =>  $create_by_userid,
			'create_by_usertype'    =>  $create_by_usertype,
			'randomid'              =>  $randomId,
		);

		db_insert("school_gallery", $aryData);

		$_SESSION['success'] = "Submitted Successfully";
		redirect($FileName);
		unset($_POST);
	} else {
		$stat['error'] = $validate->errors();
	}
}

// ============================================================================
// UPDATE RECORD
// ============================================================================
elseif (isset($_POST['update'])) {
	if ($validate->validate() && count($stat) == 0) {
		$newfile = $_POST['image_old'] ?? '';

		if (isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"])) {
			$filename = basename($_FILES['image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.') + 1));
			if (in_array($ext1, array('jpg', 'png', 'gif', 'jpeg'))) {
				$newfile = md5(time()) . "_" . $filename;
				move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $newfile);
			}
		}

		$aryData = array(
			'image'     =>  $newfile,
			'status'    =>  $_POST['status'],
		);

		db_update("school_gallery", $aryData, "randomid = ?", [$_GET['randomid']]);

		$_SESSION['success'] = "Update Successfully";
		unset($_POST);
		redirect($FileName);
	} else {
		$stat['error'] = $validate->errors();
	}
}

// ============================================================================
// DELETE RECORD
// ============================================================================
elseif (($action == 'delete')) {
	db_delete("school_gallery", "randomid = ?", [$_GET['randomid']]);
	$_SESSION['success'] = 'Deleted Successfully';
	redirect($FileName);
}

// ============================================================================
// HELPER FUNCTIONS (if not already defined in config)
// ============================================================================
if (!function_exists('db_get_val')) {
	function db_get_val($query, $params = [])
	{
		global $db;
		return $db->getVal($query, $params);
	}
}

if (!function_exists('db_get_row')) {
	function db_get_row($query, $params = [])
	{
		global $db;
		return $db->getRow($query, $params);
	}
}

if (!function_exists('db_get_rows')) {
	function db_get_rows($query, $params = [])
	{
		global $db;
		return $db->getRows($query, $params);
	}
}

if (!function_exists('db_insert')) {
	function db_insert($table, $data)
	{
		global $db;
		return $db->insertAry($table, $data);
	}
}

if (!function_exists('db_update')) {
	function db_update($table, $data, $where, $params = [])
	{
		global $db;
		return $db->updateAry($table, $data, "WHERE " . $where);
	}
}

if (!function_exists('db_delete')) {
	function db_delete($table, $where, $params = [])
	{
		global $db;
		return $db->delete($table, "WHERE " . $where);
	}
}
?>
<!DOCTYPE html>
<html>

<head>
	<?php include('inc.meta.php'); ?>
</head>

<body class="fixed-left">
	<div id="wrapper">
		<?php include('inc.header.php'); ?>
		<?php include('inc.sideleft.php'); ?>
		<div class="content-page">
			<!-- Start content -->
			<div class="content">
				<div class="container">
					<!-- Page-Title -->
					<div class="row">
						<div class="col-sm-12">
							<h4 class="page-title"><?php echo $PageTitle; ?></h4>
							<ol class="breadcrumb">
								<li> <a href="<?php echo $iClassName; ?>">Home</a> </li>
								<li class="active"> <?php echo $PageTitle; ?> </li>
							</ol>
						</div>
					</div>
					<!-- Basic Form Wizard -->
					<div class="row">
						<div class="col-md-12">
							<div class="card-box aplhanewclass">
								<div class="row">
									<div class="col-md-9"> <?php echo msg($stat); ?> </div>
									<div class="col-md-3">
										<a href="<?php echo $FileName; ?>?action=add" class="btn btn-default" style="float:right">Add New Record</a>
									</div>
								</div>
							</div>
							<?php if ($action == 'add') { ?>
								<div class="card-box">
									<form role="form" action="" method="post" enctype="multipart/form-data">
										<section>

											<div class="form-group clearfix">
												<label class="col-lg-2 control-label " for="userName">Image </label>
												<div class="col-lg-10">
													<input type="file" class="form-control" name="image" value="<?php echo $_POST['image'] ?? ''; ?>" required>
												</div>
											</div>

											<div class="form-group clearfix">
												<label class="col-lg-2 control-label " for="confirm">Status </label>
												<div class="col-lg-10">
													<select class=" form-control" name="status">
														<option value="1" <?php if (isset($_POST['status']) && $_POST['status'] == '1') {
																				echo "selected";
																			} ?>>Active</option>
														<option value="0" <?php if (isset($_POST['status']) && $_POST['status'] == '0') {
																				echo "selected";
																			} ?>>Inactive</option>
													</select>
												</div>
											</div>

											<button type="submit" name="submit" class="btn btn-default">Submit</button>
											<a href="<?php echo $FileName; ?>" class="btn btn-default">Back</a>
										</section>
									</form>
								</div>
							<?php } elseif ($action == 'edit') {
								$aryDetail = db_get_row("SELECT * FROM school_gallery WHERE randomid = ?", [$_GET['randomid']]);
							?>
								<div class="card-box">
									<form role="form" action="" method="post" enctype="multipart/form-data">
										<section>

											<div class="form-group clearfix">
												<label class="col-lg-2 control-label" for="userName">Image </label>
												<div class="col-lg-10">
													<input type="file" class="form-control" name="image">
													<input type="hidden" class="form-control" name="image_old" value="<?php echo $aryDetail['image'] ?? ''; ?>">
													<?php if (!empty($aryDetail['image'])): ?>
														<img src="../uploads/<?php echo $aryDetail['image']; ?>" style="height:50px;">
													<?php endif; ?>
												</div>
											</div>

											<div class="form-group clearfix">
												<label class="col-lg-2 control-label " for="confirm">Status </label>
												<div class="col-lg-10">
													<select class=" form-control" name="status">
														<option value="1" <?php if (isset($aryDetail['status']) && $aryDetail['status'] == '1') {
																				echo "selected";
																			} ?>>Active</option>
														<option value="0" <?php if (isset($aryDetail['status']) && $aryDetail['status'] == '0') {
																				echo "selected";
																			} ?>>Inactive</option>
													</select>
												</div>
											</div>

											<button type="submit" name="update" class="btn btn-default">Submit</button>
											<a href="<?php echo $FileName; ?>" class="btn btn-default">Back</a>
										</section>
									</form>
								</div>
							<?php } elseif ($action == 'view') {
								$GetEmailId = db_get_row("SELECT * FROM school_gallery WHERE randomid = ?", [$_GET['randomid']]);
							?>
								<div class="card-box">
									<section>
										<div class="form-group clearfix">
											<label class="col-lg-2 control-label " for="userName">Image :</label>
											<?php if (!empty($GetEmailId['image'])): ?>
												<img src="../uploads/<?php echo $GetEmailId['image']; ?>" style="height:50px;">
											<?php endif; ?>
										</div>

										<div class="form-group clearfix">
											<label class="col-lg-2 control-label " for="userName">Status :</label>
											<?php
											if (isset($GetEmailId['status']) && $GetEmailId['status'] == '1') {
												echo "Active";
											} elseif (isset($GetEmailId['status']) && $GetEmailId['status'] == '0') {
												echo "Inactive";
											}
											?>
										</div>
										<a href="<?php echo $FileName; ?>" class="btn btn-default">Back</a>
									</section>
								</div>
							<?php } else { ?>
								<div class="card-box">
									<table id="datatable" class="table table-striped table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Image</th>
												<th>Status</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$aryList = db_get_rows("SELECT * FROM school_gallery WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
											$i = 0;
											foreach ($aryList as $iList) {
												$i++;
											?>
												<tr>
													<td><?php echo $i; ?></td>
													<td>
														<?php if (!empty($iList['image'])): ?>
															<img src="../uploads/<?php echo $iList['image']; ?>" style="height:50px;">
														<?php endif; ?>
													</td>
													<td>
														<?php
														if ($iList['status'] == '1') {
															echo "Active";
														} elseif ($iList['status'] == '0') {
															echo "Inactive";
														}
														?>
													</td>
													<td>
														<a href="<?php echo $FileName; ?>?action=edit&randomid=<?php echo $iList['randomid']; ?>" class="table-action-btn">
															<i class="fa fa-pencil"></i>
														</a>
														<a href="javascript:del('<?php echo $FileName; ?>?action=delete&randomid=<?php echo $iList['randomid']; ?>')" class="table-action-btn">
															<i class="fa fa-times"></i>
														</a>
													</td>
												</tr>
											<?php } ?>
											<?php if (empty($aryList)): ?>
												<tr>
													<td colspan="4" style="text-align: center;">No records found</td>
												</tr>
											<?php endif; ?>
										</tbody>
									</table>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<?php include('inc.footer.php'); ?>
		</div>
	</div>
	<?php include('inc.js.php'); ?>
</body>

</html>