<?php

namespace Drupal\zemoga_test\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;

/**
 * Implementing a ajax form.
 */
class RequestInfoUser extends FormBase {

  /**
   * {@inheritdoc}
   */

  public static $FEMELE = 1;
  public static $MASCULINE = 2;

  public function getFormId() {
    return 'request_info_user';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $no_js_use = FALSE) {

    // We want to deal with hierarchical form values.
    $form['#tree'] = TRUE;

    $form['step'] = [
      '#type' => 'value',
      '#value' => !empty($form_state->getValue('step')) ? $form_state->getValue('step') : 1,
    ];

    switch ($form['step']['#value']) {
      case 1:
        
        $limit_validation_errors = [['step']];
        
        $form['step1'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Step 1: Personal details'),
        ];

        $form['step1']['first_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('First Name'),
          '#default_value' => $form_state->hasValue(['step1', 'first_name']) ? $form_state->getValue(['step1', 'first_name']) : '',
          '#description' => 'Type your first name',
          '#required' => TRUE,
        ];

        $form['step1']['last_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Last Name'),
          '#default_value' => $form_state->hasValue(['step1', 'last_name']) ? $form_state->getValue(['step1', 'last_name']) : '',
          '#description' => 'Type your last name',
          '#required' => TRUE,
        ];

        $form['step1']['genre'] = array(
          '#type' => 'select',
          '#title' => t('Genre'),
          '#description' => 'Select your genre',
          '#options' => array(t('Female'), t('Masculine')),
          '#required' => TRUE,
        );

        $form['step1']['date_of_birth'] = array(
          '#type' => 'date',
          '#title' => 'Date of Birth',
          '#description' => t('i.e. 09/06/2016'),
          '#required' => TRUE,
        );
        break;
      
      case 2:
        $limit_validation_errors = [['step'], ['step1']];
        $form['step1'] = [
          '#type' => 'value',
          '#value' => $form_state->getValue('step1'),
        ];
        $form['step2'] = [
          '#type' => 'fieldset',
          '#title' => t('Step 2: Ubication info'),
        ];
        $form['step2']['city'] = [
          '#type' => 'textfield',
          '#title' => $this->t('City'),
          '#default_value' => $form_state->hasValue(['step2', 'city']) ? $form_state->getValue(['step2', 'city']) : '',
          '#required' => TRUE,
        ];
        $form['step2']['phone'] = [
          '#type' => 'number',
          '#title' => $this->t('Phone'),
          '#default_value' => $form_state->hasValue(['step2', 'phone']) ? $form_state->getValue(['step2', 'phone']) : '',
        ];
        $form['step2']['address'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Address'),
          '#default_value' => $form_state->hasValue(['step2', 'address']) ? $form_state->getValue(['step2', 'address']) : '',
        ];
        break;

      case 3:

        $genre = ( $form_state->getValue(['step1','genre']) == "Femele" ) ? RequestInfoUser::$FEMELE : RequestInfoUser::$MASCULINE;

        $field  = array(
            'first_name'   =>  $form_state->getValue(['step1','first_name']),
            'last_name'   =>  $form_state->getValue(['step1','last_name']),
            'date_of_birth'   =>  $form_state->getValue(['step1','date_of_birth']),
            'gender'   =>  $genre,
            'city'   =>  $form_state->getValue(['step2','city']),
            'phone'   =>  $form_state->getValue(['step2','phone']),
            'address'   =>  $form_state->getValue(['step2','address']),
            'created_at' => date( "Y-m-d H:i:s" )
            
        );
        $query = \Drupal::database();
        $query ->insert('zemoga_test_user_data')
            ->fields($field)
            ->execute();

        $limit_validation_errors = [['step'], ['step1'], ['step2']];
        $form['step1'] = [
          '#type' => 'value',
          '#value' => $form_state->getValue('step1'),
        ];
        $form['step2'] = [
          '#type' => 'value',
          '#value' => $form_state->getValue('step2'),
        ];
        $form['step3'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Step 3: ¡Thank you!'),
        ];
        $form['step3']['finish_form'] = [
          '#type' => 'markup',
          '#markup' => "Thank you for fill this form",
        ];

        break;

    }

    $form['actions'] = ['#type' => 'actions'];
    if ($form['step']['#value'] > 1 && $form['step']['#value'] <> 3) {
      $form['actions']['prev'] = [
        '#type' => 'submit',
        '#value' => $this->t('Previous step'),
        '#limit_validation_errors' => $limit_validation_errors,
        '#submit' => ['::prevSubmit'],
        '#ajax' => [
          'wrapper' => 'user-info-request-form-content',
          'callback' => '::prompt',
        ],
      ];
    }
    if ($form['step']['#value'] != 3) {
      $form['actions']['next'] = [
        '#type' => 'submit',
        '#value' => $this->t('Next step'),
        '#submit' => ['::nextSubmit'],
        '#ajax' => [
          'wrapper' => 'user-info-request-form-content',
          'callback' => '::prompt',
        ],
      ];
    }

    if ($form['step']['#value'] == 3) {
      $form['actions']['next'] = [
        '#type' => 'submit',
        '#value' => $this->t('Go first Step'),
        '#submit' => ['::firstStep'],
        '#ajax' => [
          'wrapper' => 'user-info-request-form-content',
          'callback' => '::prompt',
        ],
      ];
    }

    $form['#prefix'] = '<div id="user-info-request-form-content">';
    $form['#suffix'] = '</div>';

    return $form;
  }

  public function prompt(array $form, FormStateInterface $form_state) {
    return $form;
  }

  public function nextSubmit(array $form, FormStateInterface $form_state) {
    $form_state->setValue('step', $form_state->getValue('step') + 1);
    $form_state->setRebuild();
    return $form;
  }

  public function firstStep(array $form, FormStateInterface $form_state) {

    $form_state->setValue(['step1','first_name'], '');
    $form_state->setValue(['step1','last_name'], '');
    $form_state->setValue(['step1','date_of_birth'], '');
    $form_state->setValue(['step1','genre'], '');
    $form_state->setValue(['step2','city'], '');
    $form_state->setValue(['step2','phone'], '');
    $form_state->setValue(['step2','address'], '');

    $form_state->setValue('step', 1);
    $form_state->setRebuild();
    return $form;
  }

  public function prevSubmit(array $form, FormStateInterface $form_state) {
    $form_state->setValue('step', $form_state->getValue('step') - 1);
    $form_state->setRebuild();
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
