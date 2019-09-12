var argv = require('minimist')(process.argv.slice(2));
var gulp = require('gulp');
var less = require('gulp-less');
var watch = require('gulp-watch');

var watchMode = argv['watch'] || false;


gulp.task('less', function() {
	return gulp.src('www/assets/less/style.less')
		.pipe(less())
		.pipe(gulp.dest('www/assets/build/'));
});

if (watchMode) {
	gulp.watch('www/assets/**/*.less', ['less']);
}

gulp.task('default', gulp.series('less'));
