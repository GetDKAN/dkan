/**
 * Drupal Collection
 * Cypress file handling credit: https://stackoverflow.com/questions/47074225/how-to-test-file-inputs-with-cypress
 */
Cypress.Commands.add("drupalLogin", (user, password) => {
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

Cypress.Commands.add('drupalLogout', () => {
    return cy.request('/user/logout');
});

Cypress.Commands.add("drupalDrushCommand", (command) => {
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
Cypress.Commands.add('uploadFile', { prevSubject: true }, (subject, fileName, fileType = '') => {
  cy.fixture(fileName,'binary').then(content => {
    const blob =  Cypress.Blob.binaryStringToBlob(content, fileType)
      const el = subject[0];
      const testFile = new File([blob], fileName, {type: fileType});
      const dataTransfer = new DataTransfer();

      dataTransfer.items.add(testFile);
      el.files = dataTransfer.files;
      cy.wrap(subject).trigger('change', { force: true });
  });
});

