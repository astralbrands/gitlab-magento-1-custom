module.exports = function (grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    sass: {
      dev: {
        options: {
          style: 'expanded',
          sourceMap: true
        },
        files: {
          'css/main.css': 'sass/main.scss',
          'css/elements.css': 'sass/elements.scss'
        }
      }
    },
    compass: {
      dev: {
        options: {
          sassDir: 'scss',
          cssDir: 'css',
          specify: ['scss/styles.scss'],
          sourcemap: true
        }
      }
    },
    watch: {
      compile: {
        files: ['sass/*.scss', 'sass/**/*.scss'],
        tasks: ['sass:dev']
      },
      compile_compass: {
        files: ['scss/*.scss', 'scss/**/*.scss'],
        tasks: ['compass:dev']
      },
      livereload: {
        options: { livereload: true },
        files: ['css/main.css', 'css/styles.css']
      }
    },
    requirejs: {
      compile: {
        options: {
          almond: false,
          wrap: true,
          preserveLicenseComments: false,
          baseUrl: "js",
          include: [
            "main"
          ],
          mainConfigFile: "js/main.js",
          out: "build/main.js",
          done: function(done, output) {
            var duplicates = require('rjs-build-analysis').duplicates(output);

            if (duplicates.length > 0) {
              grunt.log.subhead('Duplicates found in requirejs build:');
              grunt.log.warn(duplicates);
              done(new Error('r.js built duplicate modules, please check the excludes option.'));
            }
            done();
          }
        }
      }
    },

    strip : {
      main : {
        src : 'build/main.js',
        options : {
          nodes : ['console.log', 'debug', 'debugger'],
          inline: true
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-requirejs');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.registerTask('default', ['requirejs', 'sass:dev']);
  grunt.registerTask('build-dev', ['requirejs', 'sass:dist']);
  grunt.registerTask('build-css', ['sass:dev']);
  grunt.registerTask('build', ['sass:dev', 'requirejs']);
};