<?php
	
	// Config
	// Go to https://github.com/nehalvpatel/cgui for instructions
	$timezone = "America/Chicago";
	date_default_timezone_set($timezone);
	
	$rig = array(
		"Name" => "",
		"Address" => "",
		"Port" => ""
	);
	
	$config = array(
		"Temperature" => array(80, 84),
		"Fan" => array(85, 90),
		"Rejects" => array(7, 10),
		"Discards" => array(7, 10),
		"Stales" => array(7, 10)
	);
	
	$apis = array(
		
	);
	
	$fahrenheit = false;
	
	require_once("class.cgminer.php");
	
	$rig_api = new cgminerPHP($rig["Address"], $rig["Port"]);
	
	$rig_summary = $rig_api->request("summary");
	$rig_config = $rig_api->request("config");
	$rig_coin = $rig_api->request("coin");
	
	$gpu_count = $rig_config["CONFIG"]["GPU Count"];
	$asic_count = $rig_config["CONFIG"]["PGA Count"];
	$pool_count = $rig_config["CONFIG"]["Pool Count"];
	$coin = $rig_coin["COIN"]["Hash Method"];

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="author" content="Nehal Patel">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
		<link href="css/main.css" rel="stylesheet">
		<link rel="apple-touch-icon-precomposed" sizes="57x57" href="touch-icon-iphone-114.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="touch-icon-iphone-114.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="touch-icon-ipad-144.png">
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="touch-icon-ipad-144.png">
		<link rel="icon" href="favicon.png">
		<!--[if IE]><link rel="shortcut icon" href="favicon.ico"><![endif]-->
		<title><?php if (isset($rig["Name"]) && !empty($rig["Name"])) { echo $rig["Name"]; } else { echo $rig["Address"] . ":" . $rig["Port"]; } ?></title>
	</head>
	<body>
		<div class="container" id="no-more-tables">
			<h1 class="info-header page-title" style=""><?php if (isset($rig["Name"]) && !empty($rig["Name"])) { echo $rig["Name"]; } else { echo $rig["Address"] . ":" . $rig["Port"]; } ?></h1>
			<hr>
			<h1 class="info-header">Stats</h1>
			<div class="well well-small info-block">
				<strong>Date:</strong> <?php echo date("M j, Y h:i:s A") . PHP_EOL; ?>
				<hr style="margin-top: 5px; margin-bottom: 5px;">
				<strong>Miner:</strong> <?php echo $rig_summary["STATUS"]["Description"]; ?>
				<br>
				<strong>Started:</strong> <?php
					$seconds_elapsed = $rig_summary["SUMMARY"]["Elapsed"];
					$started = time() - $seconds_elapsed;
					echo '<time datetime="' . date(DATE_W3C, $started) . '">';
					echo date("M j, Y h:i:s A", $started) . '</time>' . PHP_EOL;
				?>
				<br>
				<strong>Elapsed:</strong> <?php
					$time_units = array(
						'w' => 604800,
						'd' => 86400,
						'h' => 3600,
						'm' => 60,
						's' => 1
					);

					$strs = array();

					foreach ($time_units as $name => $int) {
						if ($seconds_elapsed < $int)
							continue;
						$num = (int) ($seconds_elapsed / $int);
						$seconds_elapsed = $seconds_elapsed % $int;
						$strs[] = sprintf("%02d", $num) . $name;
					}

					echo implode(' ', $strs);
					echo PHP_EOL;
				?>
			</div>
<?php if ($gpu_count > 0 || $asic_count > 0) { ?>
			<h1 class="info-header">Mining</h1>
			<table class="table table-striped table-bordered table-hover info-block">
				<thead>
					<tr>
						<th>Status</th>
						<th>Device</th>
						<th>Rate</th>
						<th>Temp</th>
						<th>Fan Speed</th>
						<th>Fan Percent</th>
						<th>GPU Clock</th>
						<th>Memory Clock</th>
						<th>Intensity</th>
						<th>HW Errors</th>
					</tr>
				</thead>
				<tbody>
<?php
						$total_rate = 0;
						$total_errors = 0;
						
						for ($i = 0; $i < $asic_count; $i++) {
							$asic_request = $rig_api->request("pga|" . $i);
							$asic_info = $asic_request["PGA" . $i];
							
							$device_name = $asic_info["Name"] . $asic_info["ID"];
							
							$cur_rate = 0;
							
							isset($asic_info["MHS 5s"]) ? $cur_rate = $asic_info["MHS 5s"] : $cur_rate = $asic_info["MHS av"];
							
							if ($coin == "scrypt") {
								$hash_rate = $cur_rate * 1000;
								$hash_speed = "kh/s";
							} elseif ($coin == "sha256") {
								$hash_rate = $cur_rate;
								$hash_speed = "Mh/s";
							}
							
							$total_rate += $hash_rate;
							$total_errors += $asic_info["Hardware Errors"];
							
							if ($asic_info["Temperature"] >= $config["Temperature"][1]) {
								$temperature_class = "error";
							} elseif ($asic_info["Temperature"] >= $config["Temperature"][0]) {
								$temperature_class = "warning";
							} else {
								$temperature_class = "";
							}
							
?>
					<tr>
						<td data-title="Status"><?php if ($asic_info["Status"] == "Alive" && $asic_info["Enabled"] == "Y") { ?><i class="icon-ok-sign"></i><?php } else { ?><i class="icon-remove-sign"></i><?php } ?></td>
						<td data-title="Device"><?php echo $device_name; ?></td>
						<td data-title="Rate"><?php echo $hash_rate . $hash_speed ?></td>
						<td data-title="Temp" class="<?php echo $temperature_class; ?>"><?php if ($fahrenheit === true) { echo sprintf("%02.2f", (9/5) * $asic_info["Temperature"] + 32) . "°F"; } else { echo $asic_info["Temperature"] . "°C"; } ?></td>
						<td data-title="Fan Speed">n/a</td>
						<td data-title="Fan Percent" class="">n/a</td>
						<td data-title="GPU Clock">n/a</td>
						<td data-title="Memory Clock">n/a</td>
						<td data-title="Intensity">n/a</td>
						<td data-title="HW Errors"><?php echo $asic_info["Hardware Errors"]; ?></td>
					</tr>
<?php
						}
						
						for ($i = 0; $i < $gpu_count; $i++) {
							$gpu_request = $rig_api->request("gpu|" . $i);
							$gpu_info = $gpu_request["GPU" . $i];
							
							$device_name = "GPU" . $gpu_info["ID"];
							
							$average_rate = 0;
							if (isset($gpu_info["MHS 5s"])) {
								$average_rate = $gpu_info["MHS 5s"];
							} elseif (isset($gpu_info["MHS 1s"])) {
								$average_rate = $gpu_info["MHS 1s"];
							}
							
							if ($coin == "scrypt") {
								$hash_rate = $average_rate * 1000;
								$hash_speed = "kh/s";
							} elseif ($coin == "sha256") {
								$hash_rate = $average_rate;
								$hash_speed = "Mh/s";
							}
							
							$total_rate += $hash_rate;
							$total_errors += $gpu_info["Hardware Errors"];
							
							if ($gpu_info["Temperature"] >= $config["Temperature"][1]) {
								$temperature_class = "error";
							} elseif ($gpu_info["Temperature"] >= $config["Temperature"][0]) {
								$temperature_class = "warning";
							} else {
								$temperature_class = "";
							}
							
							if ($gpu_info["Fan Percent"] >= $config["Fan"][1]) {
								$fan_class = "error";
							} elseif ($gpu_info["Fan Percent"] >= $config["Fan"][0]) {
								$fan_class = "warning";
							} else {
								$fan_class = "";
							}
							
					?>
					<tr>
						<td data-title="Status"><?php if ($gpu_info["Status"] == "Alive" && $gpu_info["Enabled"] == "Y") { ?><i class="icon-ok-sign"></i><?php } else { ?><i class="icon-remove-sign"></i><?php } ?></td>
						<td data-title="Device"><?php echo $device_name; ?></td>
						<td data-title="Rate"><?php echo $hash_rate . $hash_speed ?></td>
						<td data-title="Temp" class="<?php echo $temperature_class; ?>"><?php if ($fahrenheit === true) { echo sprintf("%02.2f", (9/5) * $gpu_info["Temperature"] + 32) . "°F"; } else { echo $gpu_info["Temperature"] . "°C"; } ?></td>
						<td data-title="Fan Speed"><?php echo $gpu_info["Fan Speed"]; ?></td>
						<td data-title="Fan Percent" class="<?php echo $fan_class; ?>"><?php echo $gpu_info["Fan Percent"]; ?>%</td>
						<td data-title="GPU Clock"><?php echo $gpu_info["GPU Clock"]; ?></td>
						<td data-title="Memory Clock"><?php echo $gpu_info["Memory Clock"]; ?></td>
						<td data-title="Intensity"><?php echo $gpu_info["Intensity"]; ?></td>
						<td data-title="HW Errors"><?php echo $gpu_info["Hardware Errors"]; ?></td>
					</tr>
<?php
						}
					?>
				</tbody>
				<tfoot>
					<tr>
						<td class="total-text"><strong>Total:</strong></td>
						<td data-title="Device"><?php echo $gpu_count + $asic_count; ?></td>
						<td data-title="Rate"><?php echo $total_rate . $hash_speed; ?></td>
						<td class="dont-display"></td>
						<td class="dont-display"></td>
						<td class="dont-display"></td>
						<td class="dont-display"></td>
						<td class="dont-display"></td>
						<td class="dont-display"></td>
						<td data-title="HW Errors"><?php echo $total_errors; ?></td>
					</tr>
				</tfoot>
			</table>
<?php } ?>
<?php if ($pool_count > 0) { ?>
			<h1 class="info-header">Pools</h1>
			<table class="table table-striped table-bordered table-hover info-block">
				<thead>
					<tr>
						<th>Status</th>
						<th>Pool</th>
						<th>URL</th>
						<th>User</th>
						<th>Confirmed</th>
						<th>Accepted</th>
						<th>Rejected</th>
						<th>Discarded</th>
						<th>Stale</th>
					</tr>
				</thead>
				<tbody>
<?php
						$total_accepted = 0;
						$total_rejected = 0;
						$total_discarded = 0;
						$total_stale = 0;
						$total_confirmed = "N/A";
						
						for ($i = 0; $i < $pool_count; $i++) {
							$rig_pool = $rig_api->request("pools");
							
							$total_accepted += $rig_pool["POOL" . $i]["Accepted"];
							$total_rejected += $rig_pool["POOL" . $i]["Rejected"];
							$total_discarded += $rig_pool["POOL" . $i]["Discarded"];
							$total_stale += $rig_pool["POOL" . $i]["Stale"];
							
							if ($rig_pool["POOL" . $i]["Rejected"] > ((($config["Rejects"][1]) / 100) * $rig_pool["POOL" . $i]["Accepted"])) {
								$rejects_class = "error";
							} elseif ($rig_pool["POOL" . $i]["Rejected"] > ((($config["Rejects"][0]) / 100) * $rig_pool["POOL" . $i]["Accepted"])) {
								$rejects_class = "warning";
							} else {
								$rejects_class = "";
							}
							
							if ($rig_pool["POOL" . $i]["Discarded"] > ((($config["Discards"][1]) / 100) * $rig_pool["POOL" . $i]["Accepted"])) {
								$discards_class = "error";
							} elseif ($rig_pool["POOL" . $i]["Discarded"] > ((($config["Discards"][0]) / 100) * $rig_pool["POOL" . $i]["Accepted"])) {
								$discards_class = "warning";
							} else {
								$discards_class = "";
							}
							
							if ($rig_pool["POOL" . $i]["Stale"] > ((($config["Stales"][1]) / 100) * $rig_pool["POOL" . $i]["Accepted"])) {
								$stales_class = "error";
							} elseif ($rig_pool["POOL" . $i]["Stale"] > ((($config["Stales"][0]) / 100) * $rig_pool["POOL" . $i]["Accepted"])) {
								$stales_class = "warning";
							} else {
								$stales_class = "";
							}
							
							$confirmed_rewards = "N/A";
							$pool_data = parse_url($rig_pool["POOL" . $i]["URL"]);
							if (isset($apis[$pool_data["host"]])) {
								$api_data = json_decode(file_get_contents($apis[$pool_data["host"]]), true);
								if (isset($api_data["confirmed_rewards"])) {
									$confirmed_rewards = $api_data["confirmed_rewards"];
									if ($total_confirmed == "N/A") {
										$total_confirmed = $confirmed_rewards;
									} else {
										$total_confirmed += $confirmed_rewards;
									}
								}
							}
							
					?>
					<tr>
						<td data-title="Status"><?php if ($rig_pool["POOL" . $i]["Status"] == "Alive") { ?><i class="icon-ok-sign"></i><?php } else { ?><i class="icon-remove-sign"></i><?php } ?></td>
						<td data-title="Pool"><?php echo $i + 1; ?></td>
						<td data-title="URL" class="long-data"><?php echo $rig_pool["POOL" . $i]["URL"]; ?></td>
						<td data-title="User"><?php echo $rig_pool["POOL" . $i]["User"]; ?></td>
						<td data-title="Confirmed"><?php echo $confirmed_rewards; ?></td>
						<td data-title="Accepted"><?php echo $rig_pool["POOL" . $i]["Accepted"]; ?></td>
						<td data-title="Rejected" class="<?php echo $rejects_class; ?>"><?php echo $rig_pool["POOL" . $i]["Rejected"]; ?></td>
						<td data-title="Discarded" class="<?php echo $discards_class; ?>"><?php echo $rig_pool["POOL" . $i]["Discarded"]; ?></td>
						<td data-title="Stale" class="<?php echo $stales_class; ?>"><?php echo $rig_pool["POOL" . $i]["Stale"]; ?></td>
					</tr>
<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<td class="total-text"><strong>Total:</strong></td>
						<td data-title="Pool"><?php echo $pool_count; ?></td>
						<td class="dont-display"></td>
						<td class="dont-display"></td>
						<td data-title="Confirmed"><?php echo $total_confirmed; ?></td>
						<td data-title="Accepted"><?php echo $total_accepted; ?></td>
						<td data-title="Rejected"><?php echo $total_rejected; ?></td>
						<td data-title="Discarded"><?php echo $total_discarded; ?></td>
						<td data-title="Stale"><?php echo $total_stale; ?></td>
					</tr>
				</tfoot>
			</table>
<?php } ?>
			<footer>
				<div style="text-align: center; margin-bottom: 20px;"><a href="http://www.itspatel.com/">itsPATEL.com</a></div>
			</footer>
		</div>
		<script src="http://code.jquery.com/jquery-latest.js"></script>
		<script src="js/bootstrap.min.js"></script>
	</body>
</html>