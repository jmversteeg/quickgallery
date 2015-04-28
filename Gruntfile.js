'use strict';
module.exports = function (grunt) {

    require('load-grunt-tasks')(grunt);
    require('time-grunt')(grunt);

    grunt.initConfig({
        concat: {
            js:  {
                src:  [
                    'node_modules/photoswipe/dist/photoswipe.min.js',
                    'node_modules/photoswipe/dist/photoswipe-ui-default.min.js',
                    'src/js/quickgallery.js'
                ],
                dest: 'dist/js/quickgallery.js'
            },
            css: {
                src:  [
                    'node_modules/photoswipe/dist/photoswipe.css',
                    'node_modules/photoswipe/dist/default-skin/default-skin.css',
                    'dist/css/quickgallery.css'
                ],
                dest: 'dist/css/qg.css'
            }
        },
        sass:   {
            build: {
                files: {
                    'dist/css/quickgallery.css': [
                        'src/scss/quickgallery.scss'
                    ]
                }
            }
        },
        copy:   {
            photoswipeDeps: {
                files: [
                    {
                        expand: true,
                        cwd:    'node_modules/photoswipe/dist/default-skin/',
                        src:    ['*'],
                        dest:   'dist/css/'
                    }
                ]
            },
            jsLibs:         {
                files: [
                    {
                        expand: true,
                        cwd:    'node_modules/underscore',
                        src:    ['*.js', '*.map'],
                        dest:   'dist/js/lib/underscore'
                    }
                ]
            }
        }
    });

    grunt.registerTask('build', [
        'concat:js',
        'sass:build',
        'concat:css',
        'copy:jsLibs',
        'copy:photoswipeDeps'
    ]);

};