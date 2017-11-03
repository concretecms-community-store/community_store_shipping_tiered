<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars);

$co = Core::make('helper/lists/countries');

?>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <?= $form->label('country', t("Countries")); ?>
            <select class="form-control" multiple name="countries[]">
                <?php $selectedCountries = explode(',',$smtm->getCountries()); ?>
                <?php foreach($countryList as $code=>$country){?>
                    <option value="<?= $code?>"<?php if(in_array($code,$selectedCountries)){echo " selected";}?>><?= $country?></option>
                <?php } ?>
            </select>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?= $form->label('minimumAmount', t("Minimum Purchase Amount for this rate to apply")); ?>
            <div class="input-group">
                <div class="input-group-addon">
                    <?= Config::get('community_store.symbol'); ?>
                </div>
                <?= $form->text('minimumAmount', $smtm->getMinimumAmount() ? $smtm->getMinimumAmount() : '0'); ?>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?= $form->label('maximumAmount', t("Maximum Purchase Amount for this rate to apply")); ?>
            <div class="input-group">
                <div class="input-group-addon">
                    <?= Config::get('community_store.symbol'); ?>
                </div>
                <?= $form->text('maximumAmount', $smtm->getMaximumAmount() ? $smtm->getMaximumAmount() : '0'); ?>
            </div>
        </div>
    </div>
</div>

<h3><?php echo t('Rate Calculation'); ?></h3>

<?php if (empty($rates)) {
    $rates = array();
    $rates[] = array('rate' => '', 'weight' => '', 'label' => '');
} ?>

<div id="weightrows">

    <?php
    foreach ($rates as $rate) { ?>
        <div class="row clearfix weight-entry">
            <div class="col-md-4">
                <div class="form-group-inline">
                    <label><i class="fa fa-arrows"></i> <?php echo t('Above weight (inclusive)'); ?></label>
                </div>
                <div class="form-group">
                    <div class="input-group" >
                    <input name="weight[]" type="text" class="form-control ccm-input-text"
                           value="<?php echo $rate['weight']; ?>"/>
                        <div class="input-group-addon"><?= Config::get('community_store.weightUnit')?></div>
                    </div>

                </div>

            </div>

            <div class="col-md-7">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group-inline">
                            <label><?php echo t('Rate'); ?></label>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <?= Config::get('community_store.symbol'); ?>
                                </div>
                                <input name="rate[]" type="text" class="form-control ccm-input-text" placeholder="<?php echo t('Shipping Rate'); ?>" value="<?php echo $rate['rate']; ?>"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group-inline">
                            <label><?php echo t('Label'); ?></label>
                        </div>
                        <div class="form-group">
                            <input name="label[]" type="text" class="form-control ccm-input-text"
                                   value="<?php echo $rate['label']; ?>" placeholder="<?php echo t('Label'); ?>"/>
                        </div>

                    </div>



                </div>

            </div>

            <div class="col-md-1">
                <br />
                <button class="btn btn-danger remove-row"><i class="fa fa-trash"></i></button>
            </div>
            <hr style="clear: both"/>
        </div>

    <?php } ?>
</div>

<p>
    <button id="addrow" class="btn btn-sm btn-primary"><?php echo t('Add Another'); ?></button>
</p>

<script>
    $(document).ready(function () {

        $('#weightrows').sortable({axis: 'y'});

        $('#addrow').click(function (e) {
            var el = $('#weightrows .weight-entry:first-child').clone();
            el.find('input').val('');
            el.find('.remove-row').removeClass('hidden');
            el.appendTo('#weightrows');

            $('html, body').animate({
                scrollTop: $(this).offset().top
            }, 1000);

            e.preventDefault();
        })
    })


    $('#weightrows').on('click', '.remove-row', function (e) {
        if ($('#weightrows .pc-entry').size() == 1) {
            $('#weightrows .pc-entry:first-child').find('input').val('');
        } else {
            $(this).parent().parent().remove();
        }

        e.preventDefault();
    });

</script>

<style>
    #postcoderows .row {
        background-color: white;
    }

</style>
