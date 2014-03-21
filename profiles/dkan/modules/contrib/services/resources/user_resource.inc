<?php

function _user_resource_definition() {
  $definition = array(
    'user' => array(
      'operations' => array(
        'retrieve' => array(
          'help' => 'Retrieve a user',
          'callback' => '_user_resource_retrieve',
          'file' => array('type' => 'inc', 'module' => 'services', 'name' => 'resources/user_resource'),
          'access callback' => '_user_resource_access',
          'access arguments' => array('view'),
          'access arguments append' => TRUE,
          'args' => array(
            array(
              'name' => 'uid',
              'type' => 'int',
              'description' => 'The uid of the user to retrieve.',
              'source' => array('path' => 0),
              'optional' => FALSE,
            ),
          ),
        ),

        'create' => array(
          'help' => 'Create a user',
          'callback' => '_user_resource_create',
          'file' => array('type' => 'inc', 'module' => 'services', 'name' => 'resources/user_resource'),
          'access callback' => '_user_resource_access',
          'access arguments' => array('create'),
          'access arguments append' => FALSE,
          'args' => array(
            array(
              'name' => 'account',
              'type' => 'array',
              'description' => 'The user object',
              'source' => 'data',
              'optional' => FALSE,
            ),
          ),
        ),

        'update' => array(
          'help' => 'Update a user',
          'callback' => '_user_resource_update',
          'file' => array('type' => 'inc', 'module' => 'services', 'name' => 'resources/user_resource'),
          'access callback' => '_user_resource_access',
          'access arguments' => array('update'),
          'access arguments append' => TRUE,
          'args' => array(
            array(
              'name' => 'uid',
              'type' => 'int',
              'description' => 'Unique identifier for this user',
              'source' => array('path' => 0),
              'optional' => FALSE,
            ),
            array(
              'name' => 'data',
              'type' => 'array',
              'description' => 'The user object with updated information',
              'source' => 'data',
              'optional' => FALSE,
            ),
          ),
        ),

        'delete' => array(
          'help' => 'Delete a user',
          'callback' => '_user_resource_delete',
          'file' => array('type' => 'inc', 'module' => 'services', 'name' => 'resources/user_resource'),
          'access callback' => '_user_resource_access',
          'access arguments' => array('delete'),
          'access arguments append' => TRUE,
          'args' => array(
            array(
              'name' => 'uid',
              'type' => 'int',
              'description' => 'The id of the user to delete',
              'source' => array('path' => 0),
              'optional' => FALSE,
            ),
          ),
        ),
        'index' => array(
          'help' => 'List all users',
          'file' => array('type' => 'inc', 'module' => 'services', 'name' => 'resources/user_resource'),
          'callback' => '_user_resource_index',
          'args' => array(
            array(
              'name' => 'page',
              'optional' => TRUE,
              'type' => 'int',
              'description' => 'The zero-based index of the page to get, defaults to 0.',
              'default value' => 0,
              'source' => array('param' => 'page'),
            ),
            array(
              'name' => 'fields',
              'optional' => TRUE,
              'type' => 'string',
              'description' => 'The fields to get.',
              'default value' => '*',
              'source' => array('param' => 'fields'),
            ),
            array(
              'name' => 'parameters',
              'optional' => TRUE,
              'type' => 'array',
              'description' => 'Parameters',
              'default value' => array(),
              'source' => array('param' => 'parameters'),
            ),
            array(
              'name' => 'pagesize',
              'optional' => TRUE,
              'type' => 'int',
              'description' => 'Number of records to get per page.',
              'default value' => variable_get('services_user_index_page_size', 20),
              'source' => array('param' => 'pagesize'),
            ),
          ),
          'access arguments' => array('access user profiles'),
          'access arguments append' => FALSE,
        ),
      ),
      'actions' => array(
        'login' => array(
          'help' => 'Login a user for a new session',
          'callback' => '_user_resource_login',
          'args' => array(
            array(
              'name' => 'username',
              'type' => 'string',
              'description' => 'A valid username',
              'source' => array('data' => 'username'),
              'optional' => FALSE,
            ),
            array(
              'name' => 'password',
              'type' => 'string',
              'description' => 'A valid password',
              'source' => array('data' => 'password'),
              'optional' => FALSE,
            ),
          ),
          'access callback' => 'services_access_menu',
          'file' => array('type' => 'inc', 'module' => 'services', 'name' => 'resources/user_resource'),
        ),

        'logout' => array(
          'file' => array('type' => 'inc', 'module' => 'services', 'name' => 'resources/user_resource'),
          'help' => 'Logout a user session',
          'callback' => '_user_resource_logout',
          'access callback' => 'services_access_menu',
        ),
        'token' => array(
          'file' => array(
            'type' => 'inc',
            'module' => 'services',
            'name' => 'user_resource',
          ),
          'callback' => '_user_resource_get_token',
          'access callback' => 'services_access_menu',
          'help' => t('Returns the CSRF token.'),
        ),
      ),
      'targeted_actions' => array(
        'cancel' => array(
          'help' => 'Cancel a user',
          'callback' => '_user_resource_cancel',
          'file' => array('type' => 'inc', 'module' => 'services', 'name' => 'resources/user_resource'),
          'access callback' => '_user_resource_access',
          'access arguments' => array('cancel'),
          'access arguments append' => TRUE,
          'args' => array(
            array(
              'name' => 'uid',
              'optional' => FALSE,
              'source' => array('path' => 0),
              'type' => 'int',
              'description' => 'The id of the user to cancel.',
            ),
          ),
        ),
        'password_reset' => array(
          'access callback' => '_user_resource_access',
          'access arguments' => array('password_reset'),
          'access arguments append' => TRUE,
          'callback' => '_user_resource_password_reset',
          'args' => array(
            array(
              'name' => 'uid',
              'optional' => FALSE,
              'source' => array('path' => 0),
              'type' => 'int',
              'description' => 'The id of the user whose password to reset.',
            ),
          ),
        ),
        'resend_welcome_email' => array(
          'access callback' => '_user_resource_access',
          'access arguments' => array('resend_welcome_email'),
          'access arguments append' => TRUE,
          'callback' => '_user_resource_resend_welcome_email',
          'args' => array(
            array(
              'name' => 'uid',
              'optional' => FALSE,
              'source' => array('path' => 0),
              'type' => 'int',
              'description' => 'The id of the user whose welcome email to resend.',
            ),
          ),
        ),
      ),
    ),
  );

  $definition['user']['actions']['register'] = array_merge($definition['user']['operations']['create'], array(
    'help' => 'Register a user',
  ));

  return $definition;
}

/**
 * Get user details.
 *
 * @param $uid
 *   UID of the user to be loaded.
 *
 * @return
 *   A user object.
 *
 * @see user_load()
 */
function _user_resource_retrieve($uid) {
  $account = user_load($uid);
  if (empty($account)) {
    return services_error(t('There is no user with ID @uid.', array('@uid' => $uid)), 404);
  }

  services_remove_user_data($account);

  // Everything went right.
  return $account;
}

/**
 * Create a new user.
 *
 * This function uses drupal_form_submit() and as such expects all input to match
 * the submitting form in question.
 *
 * @param $account
 *   A object containing account information. The $account object should
 *   contain, at minimum, the following properties:
 *     - name (user name)
 *     - mail (email address)
 *     - pass (plain text unencrypted password)
 *
 *   These properties can be passed but are optional
 *     - status (0 for blocked, otherwise will be active by default)
 *     - notify (1 to notify user of new account, will not notify by default)
 *
 *  Roles can be passed in a roles property which is an associative
 *  array formatted with '<role id>' => '<role id>', not including
 *  the authenticated user role, which is given by default.
 *
 * @return
 *   The user object of the newly created user.
 */
function _user_resource_create($account) {
  // Adds backwards compatability with regression fixed in #1083242
  $account = _services_arg_value($account, 'account');

  // Load the required includes for saving profile information
  // with drupal_form_submit().
  module_load_include('inc', 'user', 'user.pages');

  // register a new user
  $form_state['values'] = $account;
  // Password may be not available as this callback is used for register as well.
  $form_state['values']['pass'] = array(
    'pass1' => isset($account['pass']) ?: '',
    'pass2' => isset($account['pass']) ?: '',
  );
  $form_state['values']['op'] = variable_get('services_user_create_button_resource_create', t('Create new account'));

  // execute the register form
  $form_state['programmed_bypass_access_check'] = FALSE;
  drupal_form_submit('user_register_form', $form_state);
  // find and store the new user into the form_state
  if(isset($form_state['values']['uid'])) {
    $form_state['user'] = user_load($form_state['values']['uid']);
  }

  // Error if needed.
  if ($errors = form_get_errors()) {
    return services_error(implode(" ", $errors), 406, array('form_errors' => $errors));
  }
  else {
    $user = array('uid' => $form_state['user']->uid);
    if ($uri = services_resource_uri(array('user', $user['uid']))) {
      $user['uri'] = $uri;
    }
    return $user;
  }
}

/**
 * Update an existing user.
 *
 * This function uses drupal_form_submit() and as such expects all input to match
 * the submitting form in question.
 *
 * @param $uid
 *   Unique identifier for this user
 * @param $account
 *   Fields to modify for this user.
 *
 * @return
 *   The modified user object.
 */
function _user_resource_update($uid, $account) {
  // Adds backwards compatability with regression fixed in #1083242
  $account = _services_arg_value($account, 'data');

  $account['uid'] = $uid;

  $account_loaded = user_load($uid);

  // Load the required includes for saving profile information
  // with drupal_form_submit().
  module_load_include('inc', 'user', 'user.pages');

  // If a profile category was passed in, use it. Otherwise default
  // to 'account' (for saving core user data.)
  $category = 'account';
  if (isset($account['category'])) {
    $category = $account['category'];
    unset($account['category']);
  }

  // Drop any passed in values into the $account var. Anything
  // unused by the form just gets ignored. We handle roles and
  // password separately.
  foreach ($account as $key => $value) {
    if ($key != 'pass' && $key != 'roles') {
      $form_state['values'][$key] = $value;
    }
  }

  // Prepare values of roles. Check user's permission before allowing changes to roles.
  if (!isset($account['roles']) || !user_access('administer users')) {
    $account['roles'] = $account_loaded->roles;
  }
  foreach ($account['roles'] as $key => $value) {
    if (!empty($value)) {
      $form_state['values']['roles'][$key] = $key;
    }
  }
  unset($form_state['values']['roles'][2]);

  // Prepare values for password.
  if (isset($account['pass'])) {
    $form_state['values']['pass']['pass1'] = $account['pass'];
    $form_state['values']['pass']['pass2'] = $account['pass'];
  }

  // If user is changing name, make sure they have permission.
  if (isset($account['name']) && $account['name'] != $account_loaded->name && !(user_access('change own username') || user_access('administer users'))) {
    return services_error(t('You are not allowed to change your username.'), 406);
  }

  $form_state['values']['op'] = variable_get('services_user_save_button_resource_update', t('Save'));
  $form_state['values']['#user_category'] = $category;
  $form_state['values']['#account'] = $account_loaded;
  $form_state['programmed_bypass_access_check'] = FALSE;
  $ret = drupal_form_submit('user_profile_form', $form_state, $account_loaded, $category);

  // Error if needed.
  if ($errors = form_get_errors()) {
    return services_error(implode(" ", $errors), 406, array('form_errors' => $errors));
  }
  else {
    services_remove_user_data($account);
    return $account;
  }
}

/**
 * Delete a user.
 *
 * @param $uid
 *   UID of the user to be deleted.
 *
 * @see user_delete()
 */
function _user_resource_delete($uid) {
  if ($uid == 1) {
    return services_error(t('The admin user cannot be deleted.'), 403);
  }

  $account = user_load($uid);
  if (empty($account)) {
    return services_error(t('There is no user with ID @uid.', array('@uid' => $uid)), 404);
  }
  user_delete($uid);

  // Everything went right.
  return TRUE;
}

/**
 * Cancel a user.
 *
 * @param $uid
 *   UID of the user to be canceled.
 *
 * @see user_cancel()
 */
function _user_resource_cancel($uid) {
  if ($uid == 1) {
    return services_error(t('The admin user cannot be canceled.'), 403);
  }
  $account = user_load($uid);
  if (empty($account)) {
    return services_error(t('There is no user with ID @uid.', array('@uid' => $uid)), 404);
  }
  $edit = array(
    'user_cancel_notify' => isset($account->data['user_cancel_notify']) ? $account->data['user_cancel_notify'] : variable_get('user_mail_status_canceled_notify', FALSE),
  );
  // This defult setting is defined under "admin/config/people/accounts".
  $default_method = variable_get('user_cancel_method', 'user_cancel_block');

  switch ($default_method) {
    case 'user_cancel_block_unpublish':
      // Unpublish nodes (current revisions).
      module_load_include('inc', 'node', 'node.admin');
      $nodes = db_select('node', 'n')->fields('n', array('nid'))->condition('uid', $account->uid)->execute()->fetchCol();
      node_mass_update($nodes, array('status' => 0));
      break;

    case 'user_cancel_reassign':
      // Anonymize nodes (current revisions).
      module_load_include('inc', 'node', 'node.admin');
      $nodes = db_select('node', 'n')->fields('n', array('nid'))->condition('uid', $account->uid)->execute()->fetchCol();
      node_mass_update($nodes, array('uid' => 0));
      // Anonymize old revisions.
      db_update('node_revision')->fields(array('uid' => 0))->condition('uid', $account->uid)->execute();
      // Clean history.
      db_delete('history')->condition('uid', $account->uid)->execute();
      break;
  }

  _user_cancel($edit, $account, $default_method);

  // Everything went right.
  return TRUE;
}

/**
 * Login a user using the specified credentials.
 *
 * Note this will transfer a plaintext password.
 *
 * @param $username
 *   Username to be logged in.
 * @param $password
 *   Password, must be plain text and not hashed.
 *
 * @return
 *   A valid session object.
 */
function _user_resource_login($username, $password) {
  global $user;

  if ($user->uid) {
    // user is already logged in
    return services_error(t('Already logged in as @user.', array('@user' => $user->name)), 406);
  }

  // Check if account is active.
  if (user_is_blocked($username)) {
    return services_error(t('The username %name has not been activated or is blocked.', array('%name' => $username)), 403);
  }

  $uid = user_authenticate($username, $password);

  // Emulate drupal native flood control
  if (empty($uid) && variable_get('services_flood_control_enabled', TRUE)) {
    _user_resource_flood_control($username);
  }

  if ($uid) {
    $user = user_load($uid);
    if ($user->uid) {
      user_login_finalize();

      $return = new stdClass();
      $return->sessid = session_id();
      $return->session_name = session_name();
      $return->token = drupal_get_token('services');

      $account = clone $user;

      services_remove_user_data($account);

      $return->user = $account;

      return $return;
    }
  }
  watchdog('user', 'Invalid login attempt for %username.', array('%username' => $username));
  return services_error(t('Wrong username or password.'), 401);
}

/**
 * Logout the current user.
 */
function _user_resource_logout() {
  global $user;

  if (!$user->uid) {
    // User is not logged in
    return services_error(t('User is not logged in.'), 406);
  }

  watchdog('user', 'Session closed for %name.', array('%name' => $user->name));

  // Destroy the current session.
  module_invoke_all('user_logout', $user);
  session_destroy();

  // Load the anonymous user.
  $user = drupal_anonymous_user();

  return TRUE;
}

/**
 *  Update the current user logout callback to the new callback with a better return value.
 */
function _user_resource_logout_update_1_1() {
  $new_set = array(
    'callback' => '_user_resource_logout_1_1',
  );
  return $new_set;
}

/**
 * Logs out the currently logged in user and returns the new user object.
 */
function _user_resource_logout_1_1() {
  global $user;

  if (!$user->uid) {
    // User is not logged in
    return services_error(t('User is not logged in.'), 406);
  }

  watchdog('user', 'Session closed for %name.', array('%name' => $user->name));

  // Destroy the current session.
  module_invoke_all('user_logout', $user);
  session_destroy();

  // Load the anonymous user.
  $user = drupal_anonymous_user();

  return $user;
}

/**
 * Send a password reset email for the specified user.
 */
function _user_resource_password_reset($uid) {
  global $language;

  $account = user_load($uid);
  if (empty($account)) {
    return services_error(t('There is no user with ID @uid.', array('@uid' => $uid)), 404);
  }

  // Mail one time login URL and instructions using current language.
  $mail = _user_mail_notify('password_reset', $account, $language);
  if (!empty($mail)) {
    watchdog('user', 'Password reset instructions mailed to %name at %email.', array('%name' => $account->name, '%email' => $account->mail));
  }
  else {
    watchdog('user', 'There was an error re-sending password reset instructions mailed to %name at %email', array('%name' => $account->name, '%email' => $account->mail));
  }
  // Everything went right.
  return TRUE;
}

/**
 * Send a welcome email for the specified user.
 */
function _user_resource_resend_welcome_email($uid) {
  global $language;

  $account = user_load($uid);
  if (empty($account)) {
    return services_error(t('There is no user with ID @uid.', array('@uid' => $uid)), 404);
  }

  $user_register = variable_get('user_register', 2);
  switch ($user_register) {
    case USER_REGISTER_ADMINISTRATORS_ONLY:
      $op = 'register_admin_created';
      break;
    case USER_REGISTER_VISITORS:
      $op = 'register_no_approval_required';
      break;
    case USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL:
      $op = 'register_pending_approval';
  }

  // Mail the welcome emaiil using current language.
  $mail = _user_mail_notify($op, $account, $language);
  if (!empty($mail)) {
    watchdog('user', 'Welcome message has been re-sent to %name at %email.', array('%name' => $account->name, '%email' => $account->mail));
  }
  else {
    watchdog('user', 'There was an error re-sending welcome message to %name at %email', array('%name' => $account->name, '%email' => $account->mail));
  }
  // Everything went right.
  return TRUE;
}

/**
 * Return an array of optionally paged uids baed on a set of criteria.
 *
 * An example request might look like
 *
 * http://domain/endpoint/user?fields=uid,name,mail&parameters[uid]=1
 *
 * This would return an array of objects with only uid, name and mail defined,
 * where uid = 1.
 *
 * @param $page
 *   Page number of results to return (in pages of 20).
 * @param $fields
 *   The fields you want returned.
 * @param $parameters
 *   An array containing fields and values used to build a sql WHERE clause
 *   indicating items to retrieve.
 * @param $page_size
 *   Integer number of items to be returned.
 * @return
 *   An array of user objects.
 *
 * @see _node_resource_index() for more notes
 */
function _user_resource_index($page, $fields, $parameters, $page_size) {
  $user_select = db_select('users', 't')
    ->orderBy('created', 'DESC');

  services_resource_build_index_query($user_select, $page, $fields, $parameters, $page_size, 'user');

  $results = services_resource_execute_index_query($user_select);

  return services_resource_build_index_list($results, 'user', 'uid');
}

/**
 * Access check callback for user resource.
 */
function _user_resource_access($op = 'view', $args = array()) {
  // Adds backwards compatability with regression fixed in #1083242
  if (isset($args[0])) {
    $args[0] = _services_access_value($args[0], array('account', 'data'));
  }

  // Check if the user exists if appropriate.
  if ($op != 'create' && $op != 'register' ) {
    $account = user_load($args[0]);
    if (!$account) {
      return services_error(t('There is no user with ID @uid.', array('@uid' => $args[0])), 406);
    }
  }

  global $user;
  switch ($op) {
    case 'view':
      return user_view_access($account);
    case 'update':
      return ($user->uid == $account->uid || user_access('administer users'));
    case 'create':
    case 'register':
      if (!$user->uid && variable_get('user_register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL) != USER_REGISTER_ADMINISTRATORS_ONLY) {
        return TRUE;
      }
      else {
        return user_access('administer users');
      }
    case 'password_reset':
      return TRUE;
    case 'delete':
    case 'cancel':
    case 'resend_welcome_email':
      return user_access('administer users');
  }
}

/**
 * Changes the user/login endpoint to accept the same parameters as the user/register endpoint, namely
 * "name" instead of "username" and "pass" instead of "password"
 */
function _user_resource_login_update_1_1() {
  $new_set = array(
    'args' => array(
      array(
        'name' => 'name',
        'type' => 'string',
        'description' => 'A valid username',
        'source' => array('data' => 'name'),
        'optional' => FALSE,
      ),
      array(
        'name' => 'pass',
        'type' => 'string',
        'description' => 'A valid password',
        'source' => array('data' => 'pass'),
        'optional' => FALSE,
      ),
    ),
  );
  return $new_set;
}

function _user_resource_get_token() {
  return array('token' => drupal_get_token('services'));
}



/**
 * Callback that emulate drupal native flood control.
 * @param $username
 *  Username we register flood event for
 * @see user_login_authenticate_validate()
 * @see user_login_final_validate()
 */
function _user_resource_flood_control($username) {
  $flood_message = '';

  // Always register an IP-based failed login event.
  flood_register_event('failed_login_attempt_ip', variable_get('user_failed_login_ip_window', 3600));

  if (!flood_is_allowed('failed_login_attempt_ip', variable_get('user_failed_login_ip_limit', 50), variable_get('user_failed_login_ip_window', 3600))) {
    $flood_message = t('This IP address is temporarily blocked.');
  }

  $account = db_query("SELECT * FROM {users} WHERE name = :name AND status = 1", array(':name' => $username))->fetchObject();

  if ($account) {
    if (variable_get('user_failed_login_identifier_uid_only', FALSE)) {
      // Register flood events based on the uid only, so they apply for any
      // IP address. This is the most secure option.
      $identifier = $account->uid;
    }
    else {
      // The default identifier is a combination of uid and IP address. This
      // is less secure but more resistant to denial-of-service attacks that
      // could lock out all users with public user names.
      $identifier = $account->uid . '-' . ip_address();
      flood_register_event('failed_login_attempt_user', variable_get('user_failed_login_user_window', 21600), $identifier);
    }

    // Don't allow login if the limit for this user has been reached.
    // Default is to allow 5 failed attempts every 6 hours.
    if (!flood_is_allowed('failed_login_attempt_user', variable_get('user_failed_login_user_limit', 5), variable_get('user_failed_login_user_window', 21600), $identifier)) {
      $flood_message = t('Account is temporarily blocked.');
    }
  }

  // Throw exception if have blocking
  if (!empty($flood_message)) {
    services_error($flood_message, 406);
  }
}