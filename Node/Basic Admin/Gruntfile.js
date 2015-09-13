module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({

    // Nodemon
    nodemon: {
      dev: {
        script: 'bin/www',
        options: {
          ignore: ['node_modules/**'],
          ext: 'js,json',
          watch: ['app.js', 'server.js', 'routes', 'sockets', 'models', 'libs', 'config']
        }
      }
    },







  });

  // Load the plugins
  grunt.loadNpmTasks('grunt-contrib-requirejs');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-nodemon');
  grunt.loadNpmTasks('grunt-mocha-test');
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-cucumber');
  grunt.loadNpmTasks('grunt-phantom');

  // Precompile-only task


  // Build-only task
  grunt.registerTask('compile', []);

  // Heroku tasks
  grunt.registerTask('postinstall', ['precompile', 'compile']);

  // Unit tests
  grunt.registerTask('test:unit', ['mochaTest:unit']);

  // Integration tests
  grunt.registerTask('test:integration', ['mochaTest:integration']);

  // Behaviour tests
  grunt.registerTask('test:behaviour', ['phantom:cucumber', 'cucumberjs']);

  // Default task for development
  grunt.registerTask('default', 'Run the server with nodemon, precompile assets watches for changes', function() {
    // Spawns a nodemon task with the server and tails the output
    var nodemon = grunt.util.spawn({
      cmd: 'grunt',
      args: ['nodemon']
    });

    // Appends server output to grunt stdout
    nodemon.stdout.pipe(process.stdout);
    nodemon.stderr.pipe(process.stderr);

    // Precompile assets on task startup
    grunt.task.run('precompile');

    // Watches for changes
    grunt.task.run('watch');
  });
};
