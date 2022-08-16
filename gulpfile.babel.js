'use strict';
'use strict';

import {src, dest, lastRun, watch, series, parallel} from 'gulp';
import stylus from 'gulp-stylus';
import nib from 'nib';
import debug from 'gulp-debug';
import plumber from 'gulp-plumber';
import notify from 'gulp-notify';
import cleanCss from 'gulp-clean-css';
import concat from 'gulp-concat';
import gulpIf from 'gulp-if';
import uglify from 'gulp-uglify';
import babel from 'gulp-babel';
import yargs from 'yargs';
import gcmq from 'gulp-group-css-media-queries';
import svgSprite from 'gulp-svg-sprite';
import concatCss from 'gulp-concat-css';
import autoPrefix from 'gulp-autoprefixer';

const PRODUCTION = yargs.argv.prod;

//FRONT
export const stylesLibsFront = () => {
    return src('app/front/styl/libs.styl')
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
                title: 'Styles libs',
                message: err.message
            }))
        }))
        .pipe(stylus({
            use: [nib()],
            'include css': true
        }))
        .pipe(cleanCss({compatibility: 'ie8'}))
        .pipe(dest('resource/front/css'))
};
export const stylesFront = () => {
    return src('app/front/styl/bundle.styl')
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
                title: 'Styles',
                message: err.message
            }))
        }))
        .pipe(debug({title: 'src'}))
        .pipe(stylus({
            use: [nib()],
            'include css': true
        }))
        .pipe(debug({title: 'stylus'}))
        .pipe(gcmq())
        .pipe(autoPrefix())
        .pipe(cleanCss({compatibility: 'ie8'}))
        .pipe(dest('resource/front/css'));
};
export const constructorComponentsStyl = () => {
    return src(`protection/modules/template/administrator-cms/resource/**/*.styl`)
        .pipe(debug({title: 'src'}))
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
                title: 'Styles',
                message: err.message
            }))
        }))
        .pipe(stylus({
            use: [nib()],
            'include css': true
        }))
        .pipe(debug({title: 'stylus'}))

        .pipe(dest(`protection/modules/template/administrator-cms/resource`))
};
export const constructorComponentsToOneFile = () => {
    return src('protection/modules/template/administrator-cms/resource/**/*.css')
        .pipe(debug({title: 'concat:'}))
        .pipe(concatCss("section.css"))
        .pipe(gcmq())
        .pipe(autoPrefix())
        .pipe(cleanCss({compatibility: 'ie8'}))
        .pipe(dest(`resource/front/css`))
}

export const libsFront = () => {
    return src([
        //all js libs include here
        //empty.js it heeds if you dont use any libs
        'node_modules/swiper/swiper-bundle.min.js',
        'app/front/js/libs/fancybox.umd.js',
        'app/front/js/libs/magic-mouse.js',
        'app/front/js/libs/lazyload.min.js',
        'node_modules/aos/dist/aos.js',
    ])
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
                title: 'Libs',
                message: err.message
            }))
        }))
        .pipe(concat('libs.min.js'))
        .pipe(uglify())
        .pipe(dest('resource/front/js'));
};
export const jsFront = () => {
    return src([
        'app/front/js/slider.js',
        'app/front/js/ajax.js',
        'app/front/js/main.js',
    ])
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
                title: 'JS',
                message: err.message
            }))
        }))
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(concat('bundle.js'))
        .pipe(uglify())
        .pipe(dest('resource/front/js'))
};
export const doSvgSprite = () => {
    return src('app/front/svg-for-sprite/*.svg')
        .pipe(svgSprite({
                mode: {
                    stack: {
                        sprite: "../svg-sprite.svg"
                    }
                },
            }
        ))
        .pipe(dest('resource/front/img/svg/'));
};

//BACK
export const stylesLibsBack = () => {
    return src('app/admin/styl/libs.styl')
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
                title: 'Styles libs',
                message: err.message
            }))
        }))
        .pipe(stylus({
            use: [nib()],
            'include css': true
        }))
        .pipe(cleanCss({compatibility: 'ie8'}))
        .pipe(dest('resource/administrator-cms/css'))
};
export const stylesBack = () => {
    return src('app/admin/styl/bundle.styl')
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
                title: 'Styles',
                message: err.message
            }))
        }))
        .pipe(debug({title: 'src'}))
        .pipe(stylus({
            use: [nib()],
            'include css': true
        }))
        .pipe(debug({title: 'stylus'}))
        .pipe(cleanCss({compatibility: 'ie8'}))
        .pipe(gulpIf(PRODUCTION, gcmq()))
        .pipe(dest('resource/administrator-cms/css'));
};
export const libsBack = () => {
    return src([
        //all js libs include here
        //empty.js it heeds if you dont use any libs
        'node_modules/jquery/dist/jquery.min.js',
        'node_modules/jquery-confirm/dist/jquery-confirm.min.js',
        'node_modules/apexcharts/dist/apexcharts.min.js',
    ])
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
                title: 'Libs',
                message: err.message
            }))
        }))
        .pipe(concat('libs.min.js'))
        .pipe(uglify())
        .pipe(dest('resource/administrator-cms/js'));
};
export const jsBack = () => {
    return src([
        'app/admin/js/libs/cookie.js',
        'app/admin/js/libs/jquery.multi-select.js',
        'app/admin/js/libs/jquery.quicksearch.js',
        'app/admin/js/libs/ui-custom.js',
        'app/admin/js/libs/tablednd.js',
        'app/admin/js/component/active.js',
        'app/admin/js/component/constructor.js',
        'app/admin/js/component/error.js',
        'app/admin/js/component/faq.js',
        'app/admin/js/component/save.js',
        'app/admin/js/component/sort.js',
        'app/admin/js/component/infoblock.js',
        'app/admin/js/component/price.js',
        'app/admin/js/component/order.js',
        'app/admin/js/main.js',
    ])
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
                title: 'JS',
                message: err.message
            }))
        }))
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(concat('bundle.js'))
        .pipe(dest('resource/administrator-cms/js'))
};
export const doSvgSpriteBack = () => {
    return src('app/admin/svg-for-sprite/*.svg')
        .pipe(svgSprite({
                mode: {
                    stack: {
                        sprite: "../svg-sprite.svg"
                    }
                },
            }
        ))
        .pipe(dest('resource/administrator-cms/images/svg/'));
};


export const watchForChanges = () => {
    watch('app/admin/styl/**/*', stylesBack);
    watch('app/admin/js/**/*.js', jsBack);
    watch('app/admin/svg-for-sprite/*.svg', doSvgSpriteBack);

    watch('app/front/styl/**/*', stylesFront);
    watch('app/front/js/**/*.js', jsFront);
    watch('protection/modules/template/administrator-cms/resource/**/*.styl', series(constructorComponentsStyl));
    watch('protection/modules/template/administrator-cms/resource/**/*.css', series(constructorComponentsToOneFile));
    watch('app/front/svg-for-sprite/*.svg', doSvgSprite);
};

export const dev = series(parallel(doSvgSprite, doSvgSpriteBack, stylesBack, stylesFront, jsBack, jsFront, libsBack, libsFront, stylesLibsBack, constructorComponentsStyl, constructorComponentsToOneFile, stylesLibsFront), watchForChanges);
export const build = parallel(doSvgSprite, doSvgSpriteBack, stylesBack, stylesFront, jsBack, jsFront, libsBack, libsFront, stylesLibsBack, constructorComponentsStyl, constructorComponentsToOneFile, stylesLibsFront);
export default dev;
