<?php

namespace Drupal\itchefer_event_enrollment\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\node\NodeInterface;

/**
 * Contains methods for the modal form when requesting to enroll in an event.
 *
 * @package Drupal\itchefer_event_enrollment\Controller
 */
class EventEnrollmentController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The ModalFormExampleController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   */
  public function __construct(FormBuilder $formBuilder, AccountProxyInterface $currentUser) {
    $this->formBuilder = $formBuilder;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('current_user')
    );
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  protected static function getDataDialogOptions() {
    return [
      'dialogClass' => 'form--default itchefer-event',
      'closeOnEscape' => TRUE,
      'width' => '582',
    ];
  }

  /**
   * Enroll dialog callback.
   */
  public function productModalDialog() {
    $response = new AjaxResponse();

    // Get the modal form using the form builder.
    $form = $this->formBuilder->getForm('Drupal\itchefer_event_enrollment\Form\ProductModalForm');

    if ($this->currentUser()->isAnonymous()) {
      $form = $this->formBuilder->getForm('Drupal\social_event\Form\EnrollRequestAnonymousForm');
      $response->addCommand(new OpenModalDialogCommand($this->t('Request to enroll'), $form, [
        'width' => '337px',
        'closeOnEscape' => TRUE,
        'dialogClass' => 'social_event-popup social_event-popup--anonymous',
      ]));
    }
    else {
      $response->addCommand(new OpenModalDialogCommand($this->t('Request to enroll'), $form, static::getDataDialogOptions()));
    }

    return $response;
  }

  /**
   * The _title_callback for the event enroll dialog route.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return string
   *   The page title.
   */
  public function enrollTitle(NodeInterface $node) {
    return $this->t('Request enrollment in @label Event', ['@label' => $node->label()]);
  }

  /**
   * Determines if user has access to enroll form.
   */
  public function enrollAccess(NodeInterface $node) {
    return AccessResult::allowed();
  }

}
