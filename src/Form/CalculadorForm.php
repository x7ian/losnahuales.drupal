<?php
/**
* @file
* Contains \Drupal\losnahuales\Form\CalculadorForm.
* Generates form for full mayan calculator
*/

namespace Drupal\losnahuales\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Url;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\losnahuales\Service\ConvertidorFechas;
use Drupal\losnahuales\Service\Tips;
use Drupal\losnahuales\Service\CustomDate;

/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a singe text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class CalculadorForm extends FormBase {

  /**
   * Build the form.
   *
   * @param array $form
   *   Default form array structure.
   * @param FormStateInterface $form_state
   *   Object containing current form state.
   * @param int $yyyy Gregorian year
   * @param int $mm Gregorian month
   * @param int $dd Gregorian day
   * @param int $sistema Gregorian sistema
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state,
                    $yyyy=NULL, $mm=NULL, $dd=NULL,
                    $sistema=NULL) {
    $do = new CustomDate();
    $submited_yyyy = $form_state->getValue('yyyy');
    if (!empty($submited_yyyy)) {
      $yyyy = $submited_yyyy;
      $mm = $form_state->getValue('mm');
      $dd = $form_state->getValue('dd');
      $epoch = $form_state->getValue('epoch');
      if ($epoch==0) {
        $yyyy *= -1;
      }
    } else if ($yyyy==NULL) {
      $ts = time();
      $date = date('Y-m-d', $ts);
      list($yyyy, $mm, $dd) = explode('-', $date);
      $epoch = 1;
    } else {}
    $jd = $do->gregorianToJd($yyyy, $mm, $dd, 0, 0, 0);
    $manana_date = $do->jdToGregorian($jd+1);
    $ayer_date = $do->jdToGregorian($jd-1);
    list($yyyy_ayer, $mm_ayer, $dd_ayer) = $ayer_date;
    list($yyyy_manana, $mm_manana, $dd_manana) = $manana_date;
    $sistema = ($sistema==NULL)? 0 : $sistema;
    $form['calculador-prev'] = array(
      '#title' => SafeMarkup::format(
        '<h5>< @subtitle</h5><p>@text</p>',
        array(
          '@subtitle' => t('Previous day'),
          '@text' => $yyyy_ayer . '/' . $mm_ayer . '/' . $dd_ayer
        )
      ),
      '#type' => 'link',
      '#url' => Url::fromRoute(
        'losnahuales.losnahuales_calcular_fecha',
        ['yyyy'=>$yyyy_ayer, 'mm'=>$mm_ayer, 'dd'=>$dd_ayer,
          'sistema'=>$sistema]
      )
    );
    $form['calculador-info'] = [
      '#type' => 'container',
      '#weight' => 0,
      '#attributes' => array(
        'class' => array(
          'calculador-info',
        ),
      ),
    ];
    $instructions = 'Enter your birthday to know your Nahual and maya date, '
      . 'or enter any gregorian date to know it\'s maya calendar equivalent.';
    $form['calculador-info']['intructions'] = [
      '#type' => 'markup',
      '#weight' => -1,
      '#markup' => '<h5>'.t($instructions).'</h5>'
    ];
    $form['calculador-info']['calculador-date'] = [
      '#type' => 'container',
      '#weight' => 0,
      '#attributes' => array(
        'class' => array(
          'calculador-date',
        ),
      ),
    ];
    $form['calculador-info']['calculador-date']['yyyy'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Year(yyyy)'),
      '#description' => $this->t('Select a gregorian calendar year'),
      '#required' => TRUE,
      '#default_value' => ($yyyy < 0)? $yyyy * (-1) : $yyyy,
      '#prefix' => '<div class="calculador-year-textfield">',
      '#suffix' => '</div>',
      '#maxlength' => '6',
      '#size' => '6',
      '#weight' => 1,
    ];
    $form['calculador-info']['calculador-date']['mm'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Month(mm)'),
      '#description' => $this->t('Select a gregorian calendar month'),
      '#required' => TRUE,
      '#default_value' => $mm,
      '#prefix' => '<div class="calculador-month-textfield">',
      '#suffix' => '</div>',
      '#maxlength' => '2',
      '#size' => '2',
      '#weight' => 2,
    ];
    $form['calculador-info']['calculador-date']['dd'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Day(dd)'),
      '#description' => $this->t('Select a gregorian calendar day'),
      '#required' => TRUE,
      '#default_value' => $dd,
      '#prefix' => '<div class="calculador-day-textfield">',
      '#suffix' => '</div>',
      '#maxlength' => '2',
      '#size' => '2',
      '#weight' => 3,
    ];

    if ($yyyy<0) {
      $epoch_default = 0;
    } else {
      $epoch_default = 1;
    }
    $form['calculador-info']['calculador-date']['epoch'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Epoch'),
      '#default_value' => $epoch_default,
      '#options' => array(
        0 => $this->t('B<small>C</small>'),
        1 => $this->t('A<small>D</small>')
      ),
      '#weight' => 4,
    );

    $convertidor = new ConvertidorFechas($yyyy . '/' . $mm . '/' . $dd);
    $sistemas = $convertidor->getSistemas();
    $options = [];
    foreach($sistemas as $id=>$sis) {
      $options[$id] = $sis['name'];
    }
    $sistema = ($sistema==NULL)? 0 : $sistema;
    $form['calculador-info']['calculador-sistema'] = [
      '#type' => 'container',
      '#weight' => 0,
      '#attributes' => array(
        'class' => array(
          'calculador-sistema',
        ),
      ),
    ];
    $form['calculador-info']['calculador-sistema']['sistema'] = [
      '#type' => 'select',
      '#title' => $this->t('Start Haab using '),
      '#options' => $options,
      '#default_value' => $sistema
    ];
    $help = Tips::get('haab_calendar_form_system');
    $form['calculador-info']['calculador-sistema']['help'] = [
      '#type' => 'markup',
      '#markup' => '<div class="help"><span class="" data-toggle="tooltip" title="' .
        $help . '">?</span></div>',
    ];
    $form['calculador-info']['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
      ]
    ];
    // Revisar
    $form['calculador-next']  = array(
      '#title' => SafeMarkup::format(
        '<h5>@subtitle ></h5><p>@text</p>',
        array(
          '@text' => $yyyy_manana . '/' . $mm_manana
            . '/' . $dd_manana,
          '@subtitle' => t('Next Day')
        )
      ),
      '#type' => 'link',
      '#url' => Url::fromRoute(
        'losnahuales.losnahuales_calcular_fecha',
        ['yyyy'=>' '.$yyyy_manana,'mm'=>$mm_manana,
          'dd'=>$dd_manana, 'sistema' =>$sistema]
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
    $epoch = $form_state->getValue('epoch');
    $yyyy = $form_state->getValue('yyyy');
    if ($epoch==0){ // BC
      $yyyy *= -1;
    }
    $mm = $form_state->getValue('mm');
    $dd = $form_state->getValue('dd');
    $sistema = $form_state->getValue('sistema');
    if ((!empty($yyyy) or $yyyy==0) and !empty($mm) and !empty($dd)) {
      if ($yyyy==0) {
        $yyyy = -1;
      }
      $form_state->setRedirect('losnahuales.losnahuales_calcular_fecha',
        ['yyyy'=>$yyyy, 'mm'=>$mm, 'dd'=>$dd, 'sistema'=>$sistema]
      );
    } else {
      $form_state->setRebuild(TRUE);
    }
    return $form;
  }
}
