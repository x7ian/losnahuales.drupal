<?php

namespace Drupal\losnahuales\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Url;
use Drupal\losnahuales\Service\ConvertidorFechas;

class TablaAbControlsForm extends FormBase {

  public function buildForm(array $form, FormStateInterface $form_state,
                    string $yyyy=NULL, string $sistema=NULL) {


    $ayer_date_formated = date('Y-m-d', $ayer);

    $form['anno'] = [
      '#type' => 'datelist',
      '#title' => $this->t('Select Gregorian Year'),
      '#description' => $this->t('Select a gregorian calendar year'),
      '#required' => TRUE,
      '#default_value' =>  $preset_date,
      '#date_part_order' => array('year'),
      '#prefix' => '<div class="calculador-date-selector">',
      '#suffix' => '</div>'
    ];
    $form['sistema'] = [
      '#type' => 'select',
      '#title' => $this->t('Select element'),
      '#options' => [
        '1' => $this->t('Tradicional'),
        '2' => $this->t('Tradicional +40'),
        '3' => $this->t('Ajuste de 13 días cada 52 Años'),
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
      ]
    ];


    $manana_date_formated = date('Y-m-d', $manana);
    $form['calculador-next']  = array(
      '#title' => SafeMarkup::format(
        '<h5>Siguiente Día ></h5><p>@text</p>',
        array('@text' => $manana_date_formated)
      ),
      '#type' => 'link',
      '#url' => Url::fromRoute(
        'losnahuales.losnahuales_calcular_fecha',
        ['yyyy'=>$yyyy_manana,'mm'=>$mm_manana,'dd'=>$dd_manana]
      ),
      '#weight' => 9999
    );


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
    return 'calculador_fecha_maya_form';
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
    $submited_date = $form_state->getValue('fecha');
    if (!empty($submited_date)) {
        $date = $submited_date->getTimeStamp();
        $yyyy = date('Y', $date);
        $mm = date('m', $date);
        $dd = date('d', $date);
        $form_state->setRedirect('losnahuales.losnahuales_calcular_fecha',
          ['yyyy'=>$yyyy, 'mm'=>$mm, 'dd'=>$dd]);
    } else {
      $form_state->setRebuild(TRUE);
    }
    return $form;
  }
}
