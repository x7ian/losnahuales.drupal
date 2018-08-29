<?php

namespace Drupal\losnahuales\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;
use Drupal\losnahuales\Service\Tips;
use Drupal\losnahuales\Service\ConvertidorFechas;
//use Drupal\Core\Url;
//use Drupal\Component\Utility\SafeMarkup;
//use Drupal\losnahuales\Service\ConvertidorFechas;

/**
 *
 * @see \Drupal\Core\Form\FormBase
 */
class CalendarioHaabForm extends FormBase {

  /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state,
                    $yyyy=NULL,
                    $sistema=NULL,
                    $comienzo=NULL) {
    $convertidor = new ConvertidorFechas($yyyy . '/1/1', $sistema);
    $form['calendario-settings'] = array(
      '#type' => 'container',
      '#weight' => 5,
      '#attributes' => array(
        'class' => array(
          'calculador-info',
        ),
      ),
    );
    $submited_date = $form_state->getValue('yyyy');
    if (!empty($submited_date)) {
      $yyyy = $submited_date;
    } else if ($yyyy==NULL) {
      $date = date('Y', time());
    }
    list($inic_yyyy, $inic_mm, $inic_dd) = $convertidor->getInicioGregoriano();
    $previous = (($yyyy-1)<$inic_yyyy)? -9999
      : ((($yyyy - 1) == 0)? -1 : $yyyy - 1);
    if ($previous!=-9999) {
      $form['calendario-anterior'] = array(
        '#title' => SafeMarkup::format(
          '<h5>@subtitle</h5><strong>@text</strong>',
          array(
            '@subtitle' => t('<&nbsp;Previous Year'),
            '@text' => $previous
          )
        ),
        '#type' => 'link',
        '#url' => Url::fromRoute(
          'losnahuales.calendario_haab',
          ['yyyy' => $previous,
          'sistema' => $sistema,
          'comienzo' => $comienzo
        ])
      );
    }

    $form['calendario-settings']['calculador-info']['yyyy'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Year(yyyy)'),
      '#description' => $this->t('Select a gregorian calendar year'),
      '#required' => TRUE,
      '#default_value' => $yyyy,
      '#prefix' => '<div class="calculador-date-selector">',
      '#suffix' => '</div>',
      '#maxlength' => '6',
      '#size' => '6',
    ];

    /*$form['calendario-settings']['calculador-info']['epoch'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Epoch'),
      '#default_value' => 1,
      '#options' => array(
        0 => $this->t('B<small>C</small>'),
        1 => $this->t('A<small>C</small>')
      ),
    );*/
    $comienzo = ($comienzo==NULL)? 0 : $comienzo;
    $form['calendario-settings']['comienzo'] = [
      '#type' => 'select',
      '#title' => $this->t('Start calendar '),
      '#options' => [
        '0' => $this->t('on january 1st.'),
        '1' => $this->t("on POP 0"),
        '2' => $this->t("one uinal previous to POP 0"),
      ],
      '#default_value' => $comienzo
    ];

    $sistemas = $convertidor->getSistemas();
    $options = [];
    foreach($sistemas as $id=>$sis) {
      $options[$id] = $sis['name'];
    }
    $sistema = ($sistema==NULL)? 0 : $sistema;
    $form['calendario-settings']['sistema'] = [
      '#type' => 'select',
      '#title' => $this->t('System'),
      '#options' => $options,
      '#default_value' => $sistema
    ];
    $help = Tips::get('haab_calendar_form_system');
    $form['calendario-settings']['help'] = [
      '#type' => 'markup',
      '#markup' => '<div class="help"><span class="" data-toggle="tooltip" title="' .
        $help . '">?</span></div>',
    ];

    $next = (($yyyy + 1) == 0)? 1 : $yyyy + 1;

    $form['calendario-proximo'] = array(
      '#title' => SafeMarkup::format(
        '<h5>@subtitle</h5><strong>@text</strong>',
        array(
          '@subtitle' => t('Next Year&nbsp;>'),
          '@text' => $next
        )
      ),
      '#type' => 'link',
      '#url' => Url::fromRoute(
        'losnahuales.calendario_haab',
        ['yyyy' => $next,
        'sistema' => $sistema,
        'comienzo' => $comienzo
      ])
    );

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        //'#ajax' => array(
          //'callback' => '\Drupal\losnahuales\Controller\PageCalculador::ajaxDisplay',
          //'event' => 'click',
          //'method' => 'html',
          //'effect' => 'fade',
          //'speed' => 'slow',
          //'prevent' => 'click',
          //'wrapper' => 'calculador-ajax-result-container',
          /*'progress' => array(
            'type' => 'throbber',
            'message' => NULL,
          ),*/
        //),
      ]
    ];
    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller.  it must
   * be unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'calendario_haab_settings_form';
  }

  /**
   * Implements form validation.
   *
   * The validateForm method is the default method called to validate input on
   * a form.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $yyyy = $form_state->getValue('yyyy');
    if (!empty($yyyy)) {
      //$yyyy = date('Y', $submited_date->getTimeStamp());
      $sistema = $form_state->getValue('sistema');
      $comienzo = $form_state->getValue('comienzo');
      $form_state->setRedirect('losnahuales.calendario_haab',
        ['yyyy'=>$yyyy, 'sistema'=>$sistema, 'comienzo' => $comienzo]
      );
    } else {
      $form_state->setRebuild(TRUE);
    }
    return $form;
  }

}
