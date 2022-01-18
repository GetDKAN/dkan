// login as a given drupal user
Cypress.Commands.add('drupalLogin', (user, password) => {
    return cy.request({
        method: 'POST',
        url: '/user/login',
        form: true,
        body: {
            name: user,
            pass: password,
            form_id: 'user_login_form'
        }
    });
});

// logout of drupal
Cypress.Commands.add('drupalLogout', () => {
    return cy.request('/user/logout');
});

// Run the supplied drush command
Cypress.Commands.add('drupalDrushCommand', (command) => {
    var cmd = Cypress.env('drupalDrushCmdLine');

    if (cmd == null) {
        cmd = 'drush %command'
    }

    if( typeof command === 'string' ) {
        command = [ command ];
    }

    const execCmd = cmd.replace('%command', command.join(' '));

    return cy.exec(execCmd);
});
