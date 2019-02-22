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
          'css/main.css': 'css/main.scss',
          'css/email-inline.css': 'css/email-inline.scss',
          'css/email-non-inline.css': 'css/email-non-inline.scss'
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
        files: ['css/*.scss', 'css/**/*.scss'],
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
          baseUrl: "js/app",
          //name: "../../born/js/lib/almond",
          include: [
           // "../lib/require",
            "../main"
            /*"router",
            "pages/common",
            "pages/home",
            "pages/login"*/
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

    // Remove console logs and debuggers
    strip : {
      main : {
        src : 'build/main.js',
        options : {
          nodes : ['console.log', 'debug', 'debugger'],
          inline: true
        }
      }
    }
/*	
  cssnano: {
    options: {
      sourcemap: true
    },
    dist: {
      files: [{
          src: 'path/src/*.css',
          dest: 'path/dist/'
      }]
    },
    subtask2: {
      files: {
        'path/dist/index.min.css': 'path/src/index.css',
        'path/dist/app.min.css': 'path/src/app.css'
      }
    }
	
  }	
*/	
  });

  grunt.loadNpmTasks('grunt-contrib-requirejs');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-compass');
  // grunt.loadNpmTasks('grunt-strip');

  grunt.registerTask('default', ['requirejs', 'sass:dev']);
  grunt.registerTask('build-dev', ['requirejs', 'sass:dist']);
  grunt.registerTask('build-css', ['sass:dev']);
  grunt.registerTask('build', ['sass:dev', 'requirejs']);
};
