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
        js: root + '/assets/js'
    },
    src: {
        js: root + '/src/js/*.js'
    },
    watch: {
        js: root + '/src/js/**/*.js'
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

    // gulp.src(path.src.js)
    //     .pipe(rigger())
    //     .pipe(uglify())
    //     .pipe(gulp.dest(path.build.js)) //Выплюнем готовый файл в build
    //     .pipe(reload({stream: true})); //И перезагрузим сервер
});

gulp.task('watch', function () {
    watch([path.watch.js], function () {
        gulp.start('js');
    });
});

gulp.task('default', [
    'js',
    'watch'
]);