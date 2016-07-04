var gulp = require('gulp'),
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
        css: root + '/src/css/*.css'
    },
    watch: {
        js: root + '/src/js/**/*.js',
        css: root + '/src/css/**/*.css'
    }
};

gulp.task('js', function () {

    gulp.src(path.src.js)
        .pipe(rigger())
        .pipe(gulp.dest(path.build.js)) //Выплюнем готовый файл в build
        .pipe(uglify())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest(path.build.js)) //Выплюнем готовый файл в build
        .pipe(reload({stream: true})); //И перезагрузим сервер
});

gulp.task('sass', function () {
    //TODO implement task sass
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