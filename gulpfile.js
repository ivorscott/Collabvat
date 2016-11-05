'use strict';
var gulp = require("gulp"), // command line task runner
    sass = require("gulp-sass"), // css with superpowers
    gutil = require("gulp-util"), // better error logs
    spawn = require('child_process').spawn, // launch command line processes
    rimraf = require('gulp-rimraf'), // delete recursively, equivalent to rm -rf command
    ignore = require('gulp-ignore'), // preserve something from rimraf deletion
    concat = require('gulp-concat'), // combine files to one file
    uglify = require('gulp-uglify'), // remove all unnecessary characters from source code
    sourcemaps = require('gulp-sourcemaps'), // reveal sass sources in browser console
    browserSync = require('browser-sync'), // refresh browser
    reload = browserSync.reload,
    webroot = './',
    assetPath = webroot + 'assets/',
    brand = 'dev.collabvat.co';

var paths = {

  //== Distribution Folders

  distSassFolder: assetPath + 'dist/css/',
  distJsFolder: assetPath + 'dist/js/',

  //== Distribution File Paths

  distCss: assetPath + 'dist/css/*.css',
  distJs: assetPath + 'dist/js/*.js',

  //== Order Specific Paths

  jquery: assetPath + 'lib/vendor/jquery/jquery.min.js',
  bootstrap: assetPath + 'lib/bootstrap-sass/assets/javascripts/bootstrap.min.js',

  //== Watch Paths

  customJs: assetPath + 'js/**/*.js',
  vendorJs: assetPath + 'lib/vendor/**/*.js',
  view: webroot + 'views/**/*.php',
  sass: assetPath + "sass/**/*.scss",
  sassMaster: assetPath + "sass/cv-brand-bootstrap.scss"
};

/* ====================================
 EMPTY DIRECTORY
 ====================================== */

//== Empty Directories With 'gulp clean'

gulp.task('clean:js', function() {
  return gulp.src(paths.distJs, { read: false })
    //.pipe(ignore('node_modules/**')) // preserve something
    .pipe(rimraf());
});
gulp.task('clean:css', function() {
  return gulp.src(paths.distCss, { read: false })
    //.pipe(ignore('node_modules/**')) // preserve something
    .pipe(rimraf());
});

gulp.task('clean', ['clean:css', 'clean:js']);

/* ====================================
 MINIFY & COMPILE TOOLS
 ====================================== */

//== Minify Javascript With 'gulp min'

gulp.task('min', function() {
  return gulp.src([ paths.jquery, paths.bootstrap,
      paths.vendorJs, paths.customJs ])
    .pipe(concat('all.js'))
    .pipe(uglify())
    .pipe(gulp.dest(paths.distJsFolder));
});

//== Compile Sass With 'gulp sass'

gulp.task('sass', function () {
  gulp.src(paths.sassMaster)
    .pipe(sourcemaps.init())
    .pipe(sass({
        outputStyle: 'compressed' // style of compiled css
    })
    .on('error', gutil.log))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(paths.distSassFolder));
});

/* ====================================
 RUN APPLICATION
 ====================================== */

//== Launch Application & Watch For Changes

gulp.task('serve',['sass'], function() {
  browserSync.init({
    proxy: brand,
    logPrefix: brand.toUpperCase()
  });

  // on changes
  gulp.watch([paths.customJs, paths.vendorJs],['min']); // minify js
  gulp.watch(paths.sass, ['sass']);
  gulp.watch([paths.view, paths.distJs, paths.distCss], {cwd: './'}, reload); // refresh browser
});

gulp.task('default', ['serve']);

/* ====================================
 MYSQL SHORTCUT
 ====================================== */

//== Run The Mysql Client

gulp.task('m', function(cb){
  var flags = [
    '--user',
    'root',
    '--database',
    brand
  ];
  var cmd = spawn('/Applications/XAMPP/xamppfiles/bin/mysql',flags, {stdio: 'inherit'});
  return cmd.on('close', cb);
});

///Applications/XAMPP/bin/mysqldump --user root collabvat > collabvat.sql
