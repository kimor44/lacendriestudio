<?php

/**
 * Class to display notifications.
 */
class Sliders_Managing_Notifications
{
  /**
   * Message to be displayed in the notification.
   *
   * @var string
   */
  private string $message;

  /**
   * Status of the notification.
   *
   * @var string
   */
  private string $status;

  /**
   * Flag for adding a closing icon.
   *
   * @var bool
   */
  private bool $is_dismissible;

  /**
   * Initialize class.
   *
   * @param string $message Message to be displayed in the notification.
   * @param string $status Status of the notification.
   * @param bool $is_dismissible Flag for adding a closing icon.
   */
  public function __construct(string $message, string $status, bool $is_dismissible = false)
  {
    $this->message = $message;
    $this->status = $status;
    $this->is_dismissible = $is_dismissible;

    add_action('admin_notices', array($this, 'render'));
  }

  /**
   * Displays warning on the admin screen.
   *
   * @return void
   */
  public function render()
  {
    $is_dismissible_class = $this->is_dismissible ? 'is-dismissible' : '';
    $class = "notice notice-" . $this->status . " " . $is_dismissible_class;
    $message = __($this->message, 'text-slider');

    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
  }
}
