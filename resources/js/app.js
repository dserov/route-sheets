require('./bootstrap');
import { Fancybox } from "@fancyapps/ui/src/Fancybox/Fancybox.js";
import "@fancyapps/ui/dist/fancybox.css";

//import ru from "@fancyapps/ui/src/Fancybox/l10n/ru";
//Fancybox.defaults.l10n = ru;

window.Fancybox = Fancybox;

window.Cookies = require("js-cookie");

window.moment = require('moment');
moment.locale('ru');

import "daterangepicker/daterangepicker.js";
import "daterangepicker/daterangepicker.css";
