var gulp = require('gulp'),
    sass = require('gulp-sass'),
    minifyCSS = require('gulp-minify-css'),
    rigger = require('gulp-rigger'),
    watch = require('gulp-watch'),
    uglify = require('gulp-uglify'),
    browserSync = require("browser-sync"),
    rename = require('gulp-rename'),
    reload = browserSync.reload;

var root = 'D:/Apache/development/wordpress/wp-content/plugins/woocommerce-nova-poshta-shipping';

var path = {
    build: {
        js: root + '/assets/js',
        css: root + '/assets/css'
    },
    src: {
        js: root + '/src/js/*.js',
        sass: root + '/src/sass/**/*.scss'
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

gulp.task('watch', function () {
    watch([path.watch.js], function () {
        gulp.start('js').start('sass');
    });
});

gulp.task('default', [
    'js',
    'sass',
    'watch'
]);