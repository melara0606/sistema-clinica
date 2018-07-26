var gulp        = require('gulp');

var browserify  = require('browserify');
var babelify    = require('babelify');
var source      = require('vinyl-source-stream');
var buffer      = require('vinyl-buffer');
var uglify      = require('gulp-uglify');
var sourcemaps  = require('gulp-sourcemaps');

gulp.task('build', function () {
  return browserify({
    entries: './libs/index.js', 
    debug: true
  })
  .transform("babelify", { 
    presets: ["env"] ,
    "plugins": ["transform-async-to-generator"]
  })
  .bundle()
  .pipe(source('main.build.js'))
  .pipe(buffer())
  //.pipe(sourcemaps.init())
  .pipe(uglify())
  //.pipe(sourcemaps.write('./maps'))
  .pipe(gulp.dest('./dist/js'))
});

gulp.task('default', ['build']);