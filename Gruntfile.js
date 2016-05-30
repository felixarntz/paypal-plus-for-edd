'use strict';
module.exports = function(grunt) {
  grunt.initConfig({
    pkg:      grunt.file.readJSON('package.json'),
    banner:     '/*!\n' +
            ' * PayPal Plus for Easy Digital Downloads\n' +
            ' * \n' +
            ' * <%= pkg.author.name %> <<%= pkg.author.email %>>\n' +
            ' */',
    pluginheader: '/*\n' +
            'Plugin Name: PayPal Plus for Easy Digital Downloads\n' +
            'Plugin URI:  <%= pkg.homepage %>\n' +
            'Description: <%= pkg.description %>\n' +
            'Version:     <%= pkg.version %>\n' +
            'Author:      <%= pkg.author.name %>\n' +
            'Author URI:  <%= pkg.author.url %>\n' +
            'License:     <%= pkg.license.name %>\n' +
            'License URI: <%= pkg.license.url %>\n' +
            'Text Domain: paypal-plus-for-edd\n' +
            'Tags:        <%= pkg.keywords.join(", ") %>\n' +
            '*/',

    clean: {
      checkout: [
        'assets/checkout.min.js'
      ]
    },

    jshint: {
      options: {
        jshintrc: 'assets/.jshintrc'
      },
      checkout: {
        src: [
          'assets/checkout.js'
        ]
      }
    },

    uglify: {
      options: {
        preserveComments: 'some',
        report: 'min'
      },
      checkout: {
        src: 'assets/checkout.js',
        dest: 'assets/checkout.min.js'
      }
    },

    usebanner: {
      options: {
        position: 'top',
        banner: '<%= banner %>'
      },
      checkout: {
        src: [
          'assets/checkout.min.js'
        ]
      }
    },

    replace: {
      header: {
        src: [
          'paypal-plus-for-edd.php'
        ],
        overwrite: true,
        replacements: [{
          from: /((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/,
          to: '<%= pluginheader %>'
        }]
      },
      author: {
        src: [
          'paypal-plus-for-edd.php',
          'includes/**/*.php'
        ],
        overwrite: true,
        replacements: [{
          from: /\*\s@author\s[^\r\n]+/,
          to: '* @author <%= pkg.author.name %> <<%= pkg.author.email %>>'
        }]
      }
    }

  });

  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-banner');
  grunt.loadNpmTasks('grunt-text-replace');

  grunt.registerTask('checkout', [
    'clean:checkout',
    'jshint:checkout',
    'uglify:checkout'
  ]);

  grunt.registerTask('plugin', [
    'replace:author',
    'replace:header'
  ]);

  grunt.registerTask('default', [
    'checkout'
  ]);

  grunt.registerTask('build', [
    'checkout',
    'plugin'
  ]);
};
