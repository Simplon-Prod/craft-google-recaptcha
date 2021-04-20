<?php
/**
 * Google Recaptcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha for Craft CMS
 *
 * @link      https://www.simplonprod.co
 * @copyright Copyright (c) 2021 Simplon.Prod
 */

namespace simplonprod\googlerecaptcha\variables;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\web\View;
use simplonprod\googlerecaptcha\GoogleRecaptcha;
use Twig\Markup;

/**
 * Google Recaptcha Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.googleRecaptcha }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Simplon.Prod
 * @package   GoogleRecaptcha
 * @since     1.0.0
 */
class GoogleRecaptchaVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Render the reCAPTCHA widget
     * {{ craft.googleRecaptcha.render(options, instantRender) }}
     *
     * @param array $options
     * @param bool $instantRender
     * @return Markup
     * @throws \craft\errors\SiteNotFoundException
     */
    public function render(array $options = [], bool $instantRender = false)
    {
        $recaptchaTag = '';
        $settings = GoogleRecaptcha::$plugin->getSettings();

        $id = $options['id'] ?? 'recaptcha-' . StringHelper::randomString(6);
        ArrayHelper::remove($options, 'id');

        $siteKey = Craft::parseEnv($settings->siteKey);
        if ((int)$settings->version === 3) {
            $recaptchaTag = self::_getV3Tag($id, $siteKey, $options);
        } else {
            $recaptchaTag = self::_getV2Tag($id, $siteKey, $options, $settings->size, $settings->theme, $settings->badge, $instantRender);
        }

        return Template::raw($recaptchaTag);
    }

    /**
     * @param string $id
     * @param string $siteKey
     * @param array $options
     * @return string
     */
    private static function _getV3Tag(string $id, string $siteKey, array $options = []): string
    {
        return Craft::$app->getView()->renderTemplate('google-recaptcha/tags/v3', [
            'id' => $id,
            'hiddenInput' => Html::hiddenInput('g-recaptcha-response', '', ArrayHelper::merge($options, ['id' => $id])),
            'siteKey' => $siteKey,
        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * @param string $id
     * @param string $siteKey
     * @param array $options
     * @param string $size
     * @param string $theme
     * @param string $badge
     * @return string
     * @throws \craft\errors\SiteNotFoundException
     */
    private static function _getV2Tag(string $id, string $siteKey, array $options = [], string $size, string $theme, string $badge, bool $instantRender): string
    {
        return Craft::$app->getView()->renderTemplate('google-recaptcha/tags/v2', [
            'callbackName' => StringHelper::camelCase($id),
            'id' => $id,
            'div' => Html::tag('div', '', ArrayHelper::merge($options, ['id' => $id])),
            'siteKey' => $siteKey,
            'size' => $size,
            'theme' => $theme,
            'badge' => $badge,
            'instantRender' => $instantRender
        ], View::TEMPLATE_MODE_CP);
    }
}
