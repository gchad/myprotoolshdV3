<?php
    /**
     * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
    defined('_JEXEC') or die();
?>
<div class="joms-page">
    <h3 class="joms-page__title">
        <?php echo JText::_('COM_COMMUNITY_PROFILE_EDIT') ?>
    </h3>

    <?php //echo $submenu; ?>

    <div class="joms-tab__bar">
        <a href="#joms-profile--information"
           class="active"><?php echo JText::_('COM_COMMUNITY_PROFILE_SETTING_INFO'); ?></a>
        <a href="#joms-profile--account"><?php echo JText::_('COM_COMMUNITY_PROFILE_SETTING_ACCOUNT'); ?></a>
    </div>

    <div id="joms-profile--information" class="joms-tab__content">

        <div class="joms-gap"></div>

        <?php if ($showProfileType) { ?>
            <div>
                <?php if ($multiprofile->id != COMMUNITY_DEFAULT_PROFILE) { ?>
                    <?php echo JText::sprintf('COM_COMMUNITY_CURRENT_PROFILE_TYPE', $multiprofile->name); ?>
                <?php } else { ?>
                    <?php echo JText::_('COM_COMMUNITY_CURRENT_DEFAULT_PROFILE_TYPE'); ?>
                <?php } ?>
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=multiprofile&task=changeprofile'); ?>"><?php echo JText::_('COM_COMMUNITY_CHANGE'); ?></a>
            </div>
        <?php } ?>

        <form name="jsform-profile-edit" id="frmSaveProfile" action="<?php echo CRoute::getURI(); ?>" method="POST"
              class="js-form">
            <?php
                foreach ($fields as $name => $fieldGroup) {
                    // if there is no field for this group, move to next.
                    if (count($fieldGroup) == 0) {
                        continue;
                    }
                    if ($name != 'ungrouped') {
                        ?>

                        <legend class="joms-form__legend"><?php echo JText::_($name); ?></legend>

                    <?php
                    }
                    ?>

                    <?php
                    foreach ($fieldGroup as $f) {
                        $f = JArrayHelper::toObject($f);

                        // DO not escape 'SELECT' values. Otherwise, comparison for
                        // selected values won't work
                        if ($f->type != 'select') {
                            $f->value = $this->escape($f->value);
                        }
                        ?>

                        <div class="joms-form__group has-privacy" for="field<?php echo $f->id; ?>">
                            <span><?php echo JText::_($f->name); ?></span>
                            <?php echo CProfileLibrary::getFieldHTML($f, ''); ?>
                            <?php echo CPrivacy::getHTML('privacy' . $f->id, $f->access); ?>
                        </div>

                    <?php
                    }
                    ?>

                <?php
                }
            ?>

            <?php if (!empty($afterFormDisplay)) { ?>
                <?php echo $afterFormDisplay; ?>
            <?php } ?>

            <div class="joms-form__group">
                <span></span>
                <input type="hidden" name="action" value="profile"/>
                <?php echo JHTML::_('form.token'); ?>
                <input type="submit" onclick="submitbutton('frmSaveProfile'); return false;"
                       class="joms-button--primary joms-button--full-small"
                       value="<?php echo JText::_('COM_COMMUNITY_SAVE_CHANGES_BUTTON'); ?>"/>
            </div>
        </form>

    </div>

    <div id="joms-profile--account" class="joms-tab__content" style="display:none;">

        <form name="jsform-profile-edit" id="frmSaveDetailProfile" action="<?php echo CRoute::getURI(); ?>"
              method="POST" class="js-form">

            <?php if (!empty($beforeFormDisplay)) { ?>
                <div class="before-form">
                    <?php echo $beforeFormDisplay; ?>
                </div>
            <?php } ?>

            <!-- username  -->
            <input type="hidden" value="<?php echo $this->escape($user->get('username')); ?>">
            
            <!-- fullname -->
            <?php if (!$isUseFirstLastName) { ?>
                <div class="joms-form__group" for="fullname">
                    <span><?php echo JText::_('COM_COMMUNITY_PROFILE_YOURNAME'); ?></span>
                    <input class="joms-input" type="text" id="name" name="name" size="40"
                           value="<?php echo $this->escape($user->get('name')); ?>"/>
                </div>
            <?php } ?>

            <!-- email -->
            <div class="joms-form__group" for="email">
                <span><?php echo JText::_('COM_COMMUNITY_EMAIL'); ?></span>
                <input type="text" class="joms-input" id="jsemail" name="jsemail"
                       value="<?php echo $this->escape($user->get('email')); ?>"/>
                <input type="hidden" id="email" name="email" value="<?php echo $user->get('email'); ?>"/>
                <input type="hidden" id="emailpass" name="emailpass" id="emailpass"
                       value="<?php echo $this->escape($user->get('email')); ?>"/>
            </div>

            <?php if (!$associated) : ?>
                <?php if ($user->get('password')) : ?>
                    <!-- password -->
                    <div class="joms-form__group" for="password">
                        <span><?php echo JText::_('COM_COMMUNITY_PASSWORD'); ?></span>
                        <input id="jspassword" name="jspassword" class="joms-input" type="password" value=""/>
                    </div>
                    <!-- verify password -->
                    <div class="joms-form__group" for="verifypassword">
                        <span><?php echo JText::_('COM_COMMUNITY_VERIFY_PASSWORD'); ?></span>
                        <input id="jspassword2" name="jspassword2" class="joms-input" type="password" value=""/>
                    </div>

                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($params)) {
                echo $params->render('params');
            }
            ?>

            <div class="joms-form__group" for="dst-offset">
                <span><?php echo JText::_('COM_COMMUNITY_DAYLIGHT_SAVING_OFFSET'); ?></span>
                <?php echo $offsetList; ?>
            </div>

            <!-- group buttons -->
            <input type="hidden" name="id" value="<?php echo $user->get('id'); ?>"/>
            <input type="hidden" name="gid" value="<?php echo $user->get('gid'); ?>"/>
            <input type="hidden" name="option" value="com_community"/>
            <input type="hidden" name="view" value="profile"/>
            <input type="hidden" name="task" value="edit"/>
            <input type="hidden" id="password" name="password"/>
            <input type="hidden" id="password2" name="password2"/>

            <?php if ($config->get('fbconnectkey') && $config->get('fbconnectsecret')) { ?>
                <div class="joms-form__group" for="verifypassword">
                    <span></span>
                    <h4 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_ASSOCIATE_FACEBOOK_LOGIN'); ?></h4>
                    <?php if ($isAdmin) { ?>
                        <div
                            class="small facebook"><?php echo JText::_('COM_COMMUNITY_ADMIN_NOT_ALLOWED_TO_ASSOCIATE_FACEBOOK'); ?></div>
                    <?php } else {
                        if ($associated) { ?>
                            <div><?php echo JText::_('COM_COMMUNITY_ACCOUNT_ALREADY_MERGED'); ?></div>
                            <div>
                                <input<?php echo !empty($fbPostStatus) ? ' checked="checked"' : ''; ?> type="checkbox"
                                                                                                       id="postFacebookStatus"
                                                                                                       name="postFacebookStatus">
                                <label for="postFacebookStatus"
                                       style="display: inline;"><?php echo JText::_('COM_COMMUNITY_ALLOW_SITE_TO_PUBLISH_UPDATES_TO_YOUR_FACEBOOK_ACCOUNT'); ?></label>
                            </div>
                        <?php } else { ?>
                            <?php echo $fbHtml; ?>
                        <?php }
                    } ?>
                </div>
            <?php } ?>

            <div class="joms-form__group">
                <span></span>
                <input type="hidden" name="action" value="detail"/>
                <?php echo JHTML::_('form.token'); ?>
                <input type="submit" class="joms-button--primary joms-button--full-small"
                       value="<?php echo JText::_('COM_COMMUNITY_SAVE_CHANGES_BUTTON'); ?>"/>
            </div>

        </form>

    </div>

</div>
