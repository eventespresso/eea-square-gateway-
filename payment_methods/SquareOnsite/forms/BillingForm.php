<?php

namespace EventEspresso\Square\payment_methods\SquareOnsite\forms;

use EE_Billing_Attendee_Info_Form;
use EE_Error;
use EE_Form_Section_HTML;
use EE_Form_Section_Proper;
use EE_Hidden_Input;
use EE_Payment_Method;
use EE_PMT_SquareOnsite;
use EE_Registry;
use EE_Template_Layout;
use EE_Transaction;
use EED_SquareOnsiteOAuth;
use EEH_Money;
use EEI_Payment;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidInterfaceException;
use EventEspresso\Square\domain\Domain;
use InvalidArgumentException;
use ReflectionException;

/**
 * Class BillingForm
 *
 * @package        Event Espresso
 * @subpackage     eea-square-gateway
 * @author         Nazar Kolivoshka
 */
class BillingForm extends EE_Billing_Attendee_Info_Form
{
    /**
     * Filepath to template files
     * @var @template_path
     */
    protected $template_path;

    /**
     * @var EE_Transaction
     */
    protected $transaction;

    /**
     * @var EE_PMT_SquareOnsite
     */
    protected $squareOnsitePmt;

    /**
     * Amount to pay in this checkout.
     * @var string
     */
    protected $payAmount = 0;


    /**
     * Class constructor.
     *
     * @param EE_Payment_Method $paymentMethod
     * @param array $options
     * @throws EE_Error
     * @throws InvalidDataTypeException
     * @throws InvalidInterfaceException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function __construct(EE_Payment_Method $paymentMethod, array $options = [])
    {
        // Check the token and refresh if needed.
        EED_SquareOnsiteOAuth::checkAndRefreshToken($paymentMethod);
        // Don't initiate if there's no transaction.
        // This may occur on a partial payment when a PM page is loaded without Square (or any other payment) select.
        if (isset($options['transaction']) && $options['transaction'] instanceof EE_Transaction) {
            if (! isset($options['template_path'])) {
                throw new EE_Error(
                    sprintf(
                        // translators: %1$s: EESquareOnsiteBillingForm. $2$s: Options template path.
                        esc_html__(
                            '%1$s instantiated without the required template_path. Please provide it in $2$s',
                            'event_espresso'
                        ),
                        __CLASS__,
                        '$options[\'template_path\']'
                    )
                );
            }

            $this->squareOnsitePmt = $options['square_onsite_pmt'];
            $this->transaction = isset($options['transaction']) ? $options['transaction'] : null;
            $this->template_path = $options['template_path'];
            EE_Registry::instance()->load_helper('Money');

            if (isset($options['amount_owing'])) {
                $this->payAmount = $options['amount_owing'];
            } elseif ($this->transaction instanceof EEI_Payment) {
                // If this is a partial payment.
                $total = EEH_Money::convert_to_float_from_localized_money($this->transaction->total());
                $paid = EEH_Money::convert_to_float_from_localized_money($this->transaction->paid());
                $owning = $total - $paid;
                $this->payAmount = ($owning > 0) ? $owning : $total;
            }
        }

        $parameters = array_replace_recursive(
            $options,
            [
                'name' => 'SquareOnsite_BillingForm',
                'html_id' => 'square-onsite-billing-form',
                'html_class' => 'squareOnsite_billingForm',
                'subsections' => [
                    'debug_content' => $this->addDebugContent($paymentMethod),
                    'square_pm_form' => $this->squareEmbeddedForm(),
                    'eea_square_token' => new EE_Hidden_Input([
                        'html_id' => 'eea-square-nonce',
                        'html_name' => 'EEA_squareToken',
                        'default' => ''
                    ]),
                ]
            ]
        );

        parent::__construct($paymentMethod, $parameters);
    }


    /**
     * Possibly adds debug content to Square billing form.
     *
     * @param EE_Payment_Method $paymentMethod
     * @return string
     * @throws EE_Error
     */
    public function addDebugContent(EE_Payment_Method $paymentMethod)
    {
        if ($paymentMethod->debug_mode()) {
            return new EE_Form_Section_Proper([
                'layout_strategy' => new EE_Template_Layout([
                    'layout_template_file' => $this->template_path . 'squareDebugInfo.template.php',
                    'template_args' => []
                ])
            ]);
        }
        return new EE_Form_Section_HTML();
    }


    /**
     * Use Square's Embedded form.
     *
     * @return EE_Form_Section_Proper
     * @throws EE_Error
     */
    public function squareEmbeddedForm()
    {
        $template_args = [];
        return new EE_Form_Section_Proper([
            'layout_strategy' => new EE_Template_Layout([
                'layout_template_file' => $this->template_path . 'squareEmbeddedForm.template.php',
                'template_args' => $template_args
            ])
        ]);
    }


    /**
     * Add scripts and localization needed for this form.
     *
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function enqueue_js()
    {
        wp_enqueue_script(
            'eea_square_js_lib',
            'https://js.squareupsandbox.com/v2/paymentform',
            ['single_page_checkout'],
            false,
            true
        );
        wp_enqueue_script(
            'eea_square_pm_js',
            EEA_SQUARE_GATEWAY_PLUGIN_URL . 'assets/js/square-payments.js',
            ['eea_square_js_lib'],
            EEA_SQUARE_GATEWAY_VERSION,
            true
        );

        // Convert money for a display format.
        $payAmount = EE_PMT_SquareOnsite::getDecimalPlaces()
            ? number_format($this->payAmount, 2, '.', '')
            : $this->payAmount;
        $squareParameters = [
            'appId'             => $this->_pm_instance->get_extra_meta(Domain::META_KEY_APPLICATION_ID, true),
            'accessToken'       => $this->_pm_instance->get_extra_meta(Domain::META_KEY_ACCESS_TOKEN, true),
            'paymentMethodSlug' => $this->_pm_instance->slug(),
            'paymentCurrency'   => EE_Registry::instance()->CFG->currency->code,
            'payButtonText'     => esc_html__('Pay', 'event_espresso'),
            'currencySign'      => EE_Registry::instance()->CFG->currency->sign,
            'payAmount'         => $payAmount,
            // The transaction ID is only used for logging errors.
            'txnId' => $this->transaction instanceof EE_Transaction ? $this->transaction->ID() : 0,
            'noSPCOError'         => esc_html__(
                // @codingStandardsIgnoreStart
                'It appears the Single Page Checkout javascript was not loaded properly! Please refresh the page and try again or contact support.',
                'event_espresso'
                // @codingStandardsIgnoreEnd
            ),
            'noSquareError'       => esc_html__(
                // @codingStandardsIgnoreStart
                'It appears that Square checkout JavaScript was not loaded properly! Please refresh the page and try again or contact support. Square payments will not be processed.',
                'event_espresso'
                // @codingStandardsIgnoreEnd
            ),
            'browserNotSupported' => esc_html__(
                // @codingStandardsIgnoreStart
                'It appears that this browser is not supported by Square. We apologize, but Square payment method won\'t work in this browser version.',
                'event_espresso'
                // @codingStandardsIgnoreEnd
            ),
            'getTokenError'       => esc_html__(
                // @codingStandardsIgnoreStart
                'There was an error while trying to get the payment token. Please refresh the page and try again or contact support.',
                'event_espresso'
                // @codingStandardsIgnoreEnd
            ),
        ];

        // Localize the script with our transaction data.
        wp_localize_script('eea_square_pm_js', 'eeaSquareParameters', $squareParameters);
        parent::enqueue_js();
    }
}