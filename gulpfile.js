var gulp = require('gulp'),
    sass = require('gulp-sass'),
    minifyCSS = require('gulp-minify-css'),
    rigger = require('gulp-rigger'),
    watch = require('gulp-watch'),
    uglify = require('gulp-uglify'),
    browserSync = require("browser-sync"),
    rename = require('gulp-rename'),
    reload = browserSync.reload;

var root = '.';
var destination = root + '/wp-content/plugins/woo-shipping-for-nova-poshta/assets';

var path = {
    build: {
        js: destination + '/js',
        css: destination + '/css',
        screen: destination
    },
    src: {
        js: root + '/src/js/*.js',
        sass: root + '/src/sass/**/*.scss',
        screen: root + '/src/screenshot/*.png'
    },
    watch: {
        js: root + '/src/js/**/*.js',
        sass: root + '/src/sass/**/*.scss'
    }
};

gulp.task('js', function () {
    gulp.src(path.src.js)
        .pipe(rigger())
        .pipe(gulp.dest(path.build.js))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(path.build.js))
        .pipe(reload({stream: true}));
});

gulp.task('sass', function () {
    gulp.src(path.src.sass)
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest(path.build.css))
        .pipe(minifyCSS({keepBreaks: true}))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(path.build.css));
});

gulp.task('screen', function () {
    gulp.src(path.src.screen)
        .pipe(gulp.dest(path.build.screen))
        .pipe(reload({stream: true}));
});

gulp.task('watch', function () {
    watch([path.watch.js], function () {
        gulp.start('js').start('sass');
    });
});

gulp.task('build', [
    'js',
    'sass',
    'screen'
]);

gulp.task('default', [
    'js',
    'sass',
    'screen',
    'watch'
]);