<div class="col-sm-4">
	<div class="accordion" id="wdcn_customize">
		<?php
			$customization_data = get_option( 'wcdn_'.$tab.'_customization' );
			$settings = wcdn_customization();
			$label    = wcdn_customization_label();
			$i = 1;
			foreach ($settings[$tab] as $key => $eachsetting) {
				if($i == 1) :
					$class = "show";
				else :
					$class = "";
				endif
				?>
				<div class="accordion-item">
			    	<h2 class="accordion-header" id="ct_acc_<?php echo $i; ?>">
			      		<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ct_acc_<?php echo $i; ?>_content" aria-expanded="true" aria-controls="ct_acc_<?php echo $i; ?>_content">
			        		<?php echo $label[$key]; ?>
			      		</button>
			      		<label class="switch">
			                <input type="checkbox" name="<?php echo $tab.'['.$key.'][active]'; ?>" <?php if(isset($customization_data[$key]['active'] ) && $customization_data[$key]['active'] == 'on') { echo "checked"; } ?>>
			                <span class="slider round"></span>
			            </label>
					</h2>
					<div id="ct_acc_<?php echo $i; ?>_content" class="accordion-collapse collapse <?php echo $class; ?>" aria-labelledby="ct_acc_<?php echo $i; ?>">
						<div class="accordion-body">
							<?php 
								foreach ($eachsetting as $fieldkey => $field) {
									$id = $key.'_'.strtolower(str_replace(' ', '_', $field));
									if ($field == "Title" || $field == "Text") {
										wcdn_customization_textfield( $tab, $id, $field, $key, $customization_data );
									} elseif ($field == "Font Size") {
										wcdn_customization_numberfield( $tab, $id, $field, $key, $customization_data );
									} elseif ($field == "Text Align") {
										$option = array('left', 'right', 'center');
										wcdn_customization_selectbox( $tab, $id, $field, $key, $customization_data, $option );
									} elseif ($field == "Text Colour") {
										wcdn_customization_colorfield( $tab, $id, $field, $key, $customization_data );
									} elseif ($field == "Style") {
										$option = array('bolder', '800', 'bold', '600', '500', 'normal', '300', '200', 'lighter');
										wcdn_customization_selectbox( $tab, $id, $field, $key, $customization_data, $option );
									} elseif ($field == "Template") {
										$option = array('Default', 'Simple');
										wcdn_customization_selectbox( $tab, $id, $field, $key, $customization_data, $option );
									} elseif ($field == "Display") {
										$option = array('Comapny Logo', 'Comapany Name');
										wcdn_customization_selectbox( $tab, $id, $field, $key, $customization_data, $option );
									} elseif ($field == "Formate") {
										$option = array('m-d-Y', 'd-m-Y', 'Y-m-d', 'd/m/Y', 'd/m/y', 'd/M/y', 'd/M/Y', 'm/d/Y', 'm/d/y', 'M/d/y', 'M/d/Y');
										wcdn_customization_selectbox( $tab, $id, $field, $key, $customization_data, $option );
									}
								}
							?>
						</div>
					</div>
				</div>
				<?php
				$i++;
			}
		?>
	</div>
</div>
