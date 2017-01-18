/**
 * @file
 */

module.exports = function (grunt) {

  var globalConfig = {
    moduleName: 'slo_esri_migrate',
    endpointClass: 'MigrateDataJsonESRISlo',
    endpointClassExtends: 'MigrateDataJsonESRIBase',
    endpoint: 'http://geographicdata.slocity.opendata.arcgis.com/data.json',
  };

  grunt.initConfig({
    globalConfig: globalConfig,
    template: {
        'create-module': {
            options: {
              data: {
                'name': '<%= globalConfig.moduleName  %>',
                'endpointClass': '<%= globalConfig.endpointClass %>',
                'endpointClassExtends': '<%= globalConfig.endpointClassExtends %>',
                'endpoint': '<%= globalConfig.endpoint %>',
              }
            },
            files: {
              '<%= globalConfig.moduleName  %>/<%= globalConfig.moduleName  %>.module': ['src/new_module.module.tpl'],
              '<%= globalConfig.moduleName  %>/<%= globalConfig.moduleName  %>.install': ['src/new_module.install.tpl'],
              '<%= globalConfig.moduleName  %>/<%= globalConfig.moduleName  %>.info': ['src/new_module.info.tpl']
            }
          }
    },
    watch: {
      tasks: ['mkdir', 'template']
    }
  });

  grunt.loadNpmTasks('grunt-mkdir');
  grunt.loadNpmTasks('grunt-template');
  grunt.registerTask('default', ['template']);

}
