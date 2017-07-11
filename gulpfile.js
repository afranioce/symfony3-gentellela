var gulp = require('gulp'),
    less = require('gulp-less'),
    clean = require('gulp-clean'),
    concat = require('gulp-concat'),
    minifyJs = require('gulp-uglify');

var GBASE = 'node_modules/gentelella/';
var GVENDOR = GBASE + 'vendors/';

gulp.task('copy', ['clean'], function () {
    gulp.src([
        GVENDOR + 'iCheck/skins/flat/green.png',
        GVENDOR + 'iCheck/skins/flat/green@2x.png'
    ])
        .pipe(gulp.dest('web/assets/css/'));

    return gulp.src([
        GBASE + 'production/{css,less,js}/**/*',
        GBASE + 'production/**/{loading.gif,user.png,paypal2.png}',
        GBASE + 'build/**/*'
    ])
        .pipe(gulp.dest('web-src'));
});

gulp.task('less', function () {
    return gulp.src([
        'web-src/less/*.less',
        GVENDOR + 'bootstrap/less/bootstrap.less',
        GVENDOR + 'fontawesome/less/font-awesome.less',
        'web-src/css/custom.min.css',
        'web-src/css/**/*.css',
        'web/bundles/*/css/*.{less,css}'
    ])
        .pipe(less({compress: true}))
        .pipe(concat('style.css'))
        .pipe(gulp.dest('web/assets/css/'));
});

gulp.task('less-admin', function () {
    return gulp.src([
        GVENDOR + 'switchery/dist/switchery.css',
        GVENDOR + 'iCheck/skins/flat/green.css',
        GVENDOR + 'google-code-prettify/bin/prettify.min.css',
        GVENDOR + 'select2/dist/css/select2.min.css',
        GVENDOR + 'bootstrap-wysiwyg/css/style.css'
    ])
        .pipe(less({compress: true}))
        .pipe(concat('style_admin.css'))
        .pipe(gulp.dest('web/assets/css/'));
});

gulp.task('images', function () {
    return gulp.src([
        'web-src/images/*',
        'web/bundles/**/images/*.{gif,jpg,jpeg,png}'
    ])
        .pipe(gulp.dest('web/assets/images/'))
});

gulp.task('fonts', function () {
    return gulp.src([
        GVENDOR + 'bootstrap/fonts/*',
        GVENDOR + 'fontawesome/fonts/*'
    ])
        .pipe(gulp.dest('web/assets/fonts/'))
});

gulp.task('clean', function () {
    return gulp.src([
        'web-src',
        'web/assets/css/*',
        'web/assets/js/*',
        'web/assets/images/*',
        'web/assets/fonts/*'
    ])
        .pipe(clean());
});

gulp.task('watch', function () {
    var less = gulp.watch('web/bundles/*/css/*.{less,css}', ['less']),
        js = gulp.watch('web/bundles/*/js/*.js', ['js-app']);
});

gulp.task('js-app', function () {
    return gulp.src([
        'web/bundles/*/js/*.js'
    ])
        .pipe(concat('app.js'))
        .pipe(minifyJs())
        .pipe(gulp.dest('web/assets/js/'));
});

gulp.task('js-admin', function () {
    return gulp.src([
        GVENDOR + 'jquery/dist/jquery.min.js',
        'vendor/ninsuo/symfony-collection/jquery.collection.js',
        GVENDOR + 'bootstrap/dist/js/bootstrap.js',
        GVENDOR + 'fastclick/lib/fastclick.js',
        GVENDOR + 'iCheck/icheck.min.js',
        GVENDOR + 'nprogress/nprogress.js',
        GVENDOR + 'switchery/dist/switchery.min.js',
        GVENDOR + 'select2/dist/js/select2.full.min.js',
        GVENDOR + 'ion.rangeSlider/js/ion.rangeSlider.min.js',
        GVENDOR + 'moment/min/moment.min.js',
        GVENDOR + 'moment/min/moment-with-locales.js',
        GVENDOR + 'bootstrap-daterangepicker/daterangepicker.js',
        GVENDOR + 'jquery.inputmask/dist/min/**',
        GVENDOR + 'bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js',
        GVENDOR + 'jquery.hotkeys/jquery.hotkeys.js',
        GVENDOR + 'jQuery-Smart-Wizard/js/jquery.smartWizard.js',
        GVENDOR + 'google-code-prettify/src/prettify.js'
    ])
        .pipe(concat('app_admin.js'))
        .pipe(minifyJs())
        .pipe(gulp.dest('web/assets/js/'));
});

gulp.task('js-pages', function () {
    return gulp.src([
        'web-src/js/**/*.js'
    ])
        .pipe(minifyJs())
        .pipe(gulp.dest('web/assets/js/'));
});

gulp.task('build', ['copy'], function () {
    var tasks = ['images', 'fonts', 'less', 'less-admin', 'js-admin', 'js-app', 'js-pages'];
    tasks.forEach(function (val) {
        gulp.start(val);
    });
});

gulp.task('default', ['clean', 'build']);
