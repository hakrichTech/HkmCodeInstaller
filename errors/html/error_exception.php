<?php

use Hkm_code\Application;
use Hkm_code\Vezirion\ServicesSystem;

 $error_id = uniqid('error', true); 
 
 $title = "Error";
 ?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="robots" content="noindex">

	<title><?= hkm_esc("Error") ?></title>
	<style type="text/css">
		<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
	</style>

	<script type="text/javascript">
		<?= file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.js') ?>
	</script>
</head>
<body onload="init()">

	<!-- Header -->
	<div class="header">
		<div class="container">
			<h1><?= hkm_esc($title), hkm_esc($exception->getCode() ? ' #' . $exception->getCode() : '') ?></h1>
			<p>
				<?= nl2br(hkm_esc($exception->getMessage())) ?>
				<a href="https://www.duckduckgo.com/?q=<?= urlencode($title . ' ' . preg_replace('#\'.*\'|".*"#Us', '', $exception->getMessage())) ?>"
				   rel="noreferrer" target="_blank">search &rarr;</a>
			</p>
		</div>
	</div>

	<!-- Source -->
	<div class="container">
		<p><b><?= hkm_esc(hkm_clean_path($file??"", $line)) ?></b> at line <b><?= hkm_esc($line) ?></b></p>

		<?php if (is_file($file)) : ?>
			<div class="source">
				<?= static::highlightFile($file, $line, 15); ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="container">

		<ul class="tabs" id="tabs">
			<li><a href="#backtrace">Backtrace</a></li>
			<li><a href="#server">Server</a></li>
			<li><a href="#request">Request</a></li>
			<li><a href="#response">Response</a></li>
			<li><a href="#files">Files</a></li>
			<li><a href="#memory">Memory</a></li>
		</ul>

		<div class="tab-content">

			<!-- Backtrace -->
			<div class="content" id="backtrace">

				<ol class="trace">
				<?php foreach ($trace as $index => $row) : ?>

					<li>
						<p>
							<!-- Trace info -->
							<?php if (isset($row['file']) && is_file($row['file'])) :?>
								<?php
                                if (isset($row['function']) && in_array($row['function'], ['include', 'include_once', 'require', 'require_once'], true)) {
                                    echo hkm_esc($row['function'] . ' ' . hkm_clean_path($row['file']));
                                } else {
                                    echo hkm_esc(hkm_clean_path($row['file']) . ' : ' . $row['line']);
                                }
                                ?>
							<?php else : ?>
								{PHP internal code}
							<?php endif; ?>

							<!-- Class/Method -->
							<?php if (isset($row['class'])) : ?>
								&nbsp;&nbsp;&mdash;&nbsp;&nbsp;<?= hkm_esc($row['class'] . $row['type'] . $row['function']) ?>
								<?php if (! empty($row['args'])) : ?>
									<?php $args_id = $error_id . 'args' . $index ?>
									( <a href="#" onclick="return toggle('<?= hkm_esc($args_id, 'attr') ?>');">arguments</a> )
									<div class="args" id="<?= hkm_esc($args_id, 'attr') ?>">
										<table cellspacing="0">

										<?php
                                        $params = null;
                                        // Reflection by name is not available for closure function
                                        if (substr($row['function'], -1) !== '}') {
                                            $mirror = isset($row['class']) ? new \ReflectionMethod($row['class'], $row['function']) : new \ReflectionFunction($row['function']);
                                            $params = $mirror->getParameters();
                                        }

                                        foreach ($row['args'] as $key => $value) : ?>
											<tr>
												<td><code><?= hkm_esc(isset($params[$key]) ? '$' . $params[$key]->name : "#{$key}") ?></code></td>
												<td><pre><?= hkm_esc(print_r($value, true)) ?></pre></td>
											</tr>
										<?php endforeach ?>

										</table>
									</div>
								<?php else : ?>
									()
								<?php endif; ?>
							<?php endif; ?>

							<?php if (! isset($row['class']) && isset($row['function'])) : ?>
								&nbsp;&nbsp;&mdash;&nbsp;&nbsp;	<?= hkm_esc($row['function']) ?>()
							<?php endif; ?>
						</p>

						<!-- Source? -->
						<?php if (isset($row['file']) && is_file($row['file']) && isset($row['class'])) : ?>
							<div class="source">
								<?= static::highlightFile($row['file'], $row['line']) ?>
							</div>
						<?php endif; ?>
					</li>

				<?php endforeach; ?>
				</ol>

			</div>

			<!-- Server -->
			<div class="content" id="server">
				<?php foreach (['_SERVER', '_SESSION'] as $var) : ?>
					<?php
                    if (empty($GLOBALS[$var]) || ! is_array($GLOBALS[$var])) {
                        continue;
                    } ?>

					<h3>$<?= hkm_esc($var) ?></h3>

					<table>
						<thead>
							<tr>
								<th>Key</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($GLOBALS[$var] as $key => $value) : ?>
							<tr>
								<td><?= hkm_esc($key) ?></td>
								<td>
									<?php if (is_string($value)) : ?>
										<?= hkm_esc($value) ?>
									<?php else: ?>
										<pre><?= hkm_esc(print_r($value, true)) ?></pre>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

				<?php endforeach ?>

				<!-- Constants -->
				<?php $constants = get_defined_constants(true); ?>
				<?php if (! empty($constants['user'])) : ?>
					<h3>Constants</h3>

					<table>
						<thead>
							<tr>
								<th>Key</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($constants['user'] as $key => $value) : ?>
							<tr>
								<td><?= hkm_esc($key) ?></td>
								<td>
									<?php if (is_string($value)) : ?>
										<?= hkm_esc($value) ?>
									<?php else: ?>
										<pre><?= hkm_esc(print_r($value, true)) ?></pre>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<!-- Request -->
			<div class="content" id="request">
				<?php $request = ServicesSystem::REQUEST(); ?>

				<table>
					<tbody>
						<tr>
							<td style="width: 10em">Path</td>
							<td><?= hkm_esc($request::GET_URI()) ?></td>
						</tr>
						<tr>
							<td>HTTP Method</td>
							<td><?= hkm_esc($request::GET_METHOD(true)) ?></td>
						</tr>
						<tr>
							<td>IP Address</td>
							<td><?= hkm_esc($request::GET_IP_ADDRESS()) ?></td>
						</tr>
						<tr>
							<td style="width: 10em">Is AJAX Request?</td>
							<td><?= $request::IS_AJAX() ? 'yes' : 'no' ?></td>
						</tr>
						<tr>
							<td>Is CLI Request?</td>
							<td><?= $request::IS_CLI() ? 'yes' : 'no' ?></td>
						</tr>
						<tr>
							<td>Is Secure Request?</td>
							<td><?= $request::IS_SECURE() ? 'yes' : 'no' ?></td>
						</tr>
						<tr>
							<td>User Agent</td>
							<td><?= hkm_esc($request::GET_USER_AGENT()::GET_AGENT_STRING()) ?></td>
						</tr>

					</tbody>
				</table>


				<?php $empty = true; ?>
				<?php foreach (['_GET', '_POST', '_COOKIE'] as $var) : ?>
					<?php
                    if (empty($GLOBALS[$var]) || ! is_array($GLOBALS[$var])) {
                        continue;
                    } ?>

					<?php $empty = false; ?>

					<h3>$<?= hkm_esc($var) ?></h3>

					<table style="width: 100%">
						<thead>
							<tr>
								<th>Key</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($GLOBALS[$var] as $key => $value) : ?>
							<tr>
								<td><?= hkm_esc($key) ?></td>
								<td>
									<?php if (is_string($value)) : ?>
										<?= hkm_esc($value) ?>
									<?php else: ?>
										<pre><?= hkm_esc(print_r($value, true)) ?></pre>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

				<?php endforeach ?>

				<?php if ($empty) : ?>

					<div class="alert">
						No $_GET, $_POST, or $_COOKIE Information to show.
					</div>

				<?php endif; ?>

				<?php $headers = $request::GET_HEADERS(); ?>
				<?php if (! empty($headers)) : ?>

					<h3>Headers</h3>

					<table>
						<thead>
							<tr>
								<th>Header</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($headers as $value) : ?>
							<?php
                            if (empty($value)) {
                                continue;
                            }

                            if (! is_array($value)) {
                                $value = [$value];
                            } ?>
							<?php foreach ($value as $h) : ?>
								<tr>
									<td><?= hkm_esc($h->getName(), 'html') ?></td>
									<td><?= hkm_esc($h->getValueLine(), 'html') ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endforeach; ?>
						</tbody>
					</table>

				<?php endif; ?>
			</div>

			<!-- Response -->
			<?php
                $response = ServicesSystem::RESPONSE();
                $response::SET_STATUS_CODE(http_response_code());
            ?>
			<div class="content" id="response">
				<table>
					<tr>
						<td style="width: 15em">Response Status</td>
						<td><?= hkm_esc($response::GET_STATUS_CODE() . ' - ' . $response::GET_REASON()) ?></td>
					</tr>
				</table>

				<?php $headers = $response::GET_HEADERS(); ?>
				<?php if (! empty($headers)) : ?>
					<?php natsort($headers) ?>

					<h3>Headers</h3>

					<table>
						<thead>
							<tr>
								<th>Header</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($headers as $name => $value) : ?>
							<tr>
								<td><?= hkm_esc($name, 'html') ?></td>
								<td><?= hkm_esc($response::GET_HEADER_LINE($name), 'html') ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

				<?php endif; ?>
			</div>

			<!-- Files -->
			<div class="content" id="files">
				<?php $files = get_included_files(); ?>

				<ol>
				<?php foreach ($files as $file) :?>
					<li><?= hkm_esc(hkm_clean_path($file)) ?></li>
				<?php endforeach ?>
				</ol>
			</div>

			<!-- Memory -->
			<div class="content" id="memory">

				<table>
					<tbody>
						<tr>
							<td>Memory Usage</td>
							<td><?= hkm_esc(static::describeMemory(memory_get_usage(true))) ?></td>
						</tr>
						<tr>
							<td style="width: 12em">Peak Memory Usage:</td>
							<td><?= hkm_esc(static::describeMemory(memory_get_peak_usage(true))) ?></td>
						</tr>
						<tr>
							<td>Memory Limit:</td>
							<td><?= hkm_esc(ini_get('memory_limit')) ?></td>
						</tr>
					</tbody>
				</table>

			</div>

		</div>  <!-- /tab-content -->

	</div> <!-- /container -->

	<div class="footer">
		<div class="container">

			<p>
				Displayed at <?= hkm_esc(date('H:i:sa')) ?> &mdash;
				PHP: <?= hkm_esc(PHP_VERSION) ?>  &mdash;
				HkmCodePHP: <?= hkm_esc(Application::HKM_VERSION) ?>
			</p>

		</div>
	</div>

</body>
</html>
