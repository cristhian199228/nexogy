'use strict';

const resetChildrenPhotoRefs = children => {
    if (children && children.length > 0) {
        for (let i = 0; i < children.length; i++ ) {
            if (children[i].$refs && children[i].$refs.photo) {
                children[i].$refs.photo.value = null;
            }
        }
    }
}

function getStrAndColorPcr(res) {
    const pcr = {};
    switch (res) {
        case 0:
            pcr.color = "green darken-2";
            pcr.result = "NEGATIVO"; break;
        case 1:
            pcr.color = "red darken-2";
            pcr.result = "POSITIVO"; break;
        case 2:
            pcr.color = "orange darken-2";
            pcr.result = "ANULADO"; break;
    }
    return pcr;
}

const getStrAndColorPrs = res => {
    const prs = {};
    switch (res) {
        case 1:
            prs.color = "green darken-2";
            prs.result = "NEG"; break;
        case 2:
            prs.color = "orange darken-2";
            prs.result = "IGG R"; break;
        case 3:
            prs.color = "red darken-2";
            prs.result = "IGG"; break;
        case 4:
            prs.color = "red darken-2";
            prs.result = "IGM"; break;
        case 5:
            prs.color = "red darken-2";
            prs.result = "IGM/IGG"; break;
    }
    return prs;
}


export { getStrAndColorPcr };
