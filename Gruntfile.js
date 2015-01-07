'use strict';
module.exports = function (grunt) {

    require('load-grunt-tasks')(grunt);
    require('time-grunt')(grunt);

    grunt.initConfig({
        concat: {
            js: {
                src:  [
                    'bower_components/photoswipe/dist/photoswipe.min.js',
                    'bower_components/photoswipe/dist/photoswipe-ui-default.min.js',
                    'src/js/quickgallery.js'
                ],
                dest: 'dist/js/quickgallery.js'
            }
        },
        sass:   {
            build: {
                files: {
                    'dist/css/quickgallery.css': 'src/scss/quickgallery.scss'
                }
            }
        }
    });

    grunt.registerTask('build', [
        'concat:js',
        'sass:build'
    ]);

};