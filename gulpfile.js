var gulp = require('gulp'),
    sass = require('gulp-sass'),
    minifyCSS = require('gulp-minify-css'),
    rigger = require('gulp-rigger'),
    watch = require('gulp-watch'),
    uglify = require('gulp-uglify'),
    browserSync = require("browser-sync"),
    argv = require('yargs').argv,
    rename = require('gulp-rename'),
    reload = browserSync.reload;

var root = '.';
var pluginPath = root + '/wp-content/plugins/woo-shipping-for-nova-poshta';
var svnPath = root + '/svn.wordpress.org';
var destination = pluginPath + '/assets';

var path = {
    build: {
        js: destination + '/js',
        css: destination + '/css',
        screenShots: destination
    },
    src: {
        js: root + '/src/js/*.js',
        sass: root + '/src/sass/**/*.scss',
        screenShots: root + '/src/screenshot/*.png'
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

gulp.task('build', [
    'js',
    'sass'
]);

gulp.task('default', [
    'js',
    'sass',
    'watch'
]);

gulp.task('svn:push', function () {
    gulp.src(pluginPath + '/**/*')
        .pipe(gulp.dest(svnPath + '/trunk'));
    gulp.src(pluginPath + '/**/.*')
        .pipe(gulp.dest(svnPath + '/trunk'));
    //move screen shots to assets folder
    gulp.src(path.src.screenShots)
        .pipe(gulp.dest(path.build.screenShots))
        .pipe(reload({stream: true}));
});

gulp.task('svn:tag', function () {
    var tag = argv.t;
    if (!tag) {
        throw Error("Tag is undefined. Please set arg --t");
    }
    var destinationPath = svnPath + '/tags/' + tag;
    gulp.src(pluginPath + '/**/*')
        .pipe(gulp.dest(destinationPath));
    gulp.src(pluginPath + '/**/.*')
        .pipe(gulp.dest(destinationPath));
});