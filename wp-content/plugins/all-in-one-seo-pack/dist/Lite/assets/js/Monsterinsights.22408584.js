import{a as y,m as $}from"./vuex.esm-bundler.8589b2dd.js";import{e as A}from"./em.5c60dd9c.js";import{C as L}from"./Card.24f1a534.js";import{S as E}from"./LogoGear.55b490aa.js";import{a as k}from"./Caret.42a820e0.js";import{S as P}from"./External.e98f124d.js";import{_ as M,c as o,f as s,d as l,w as _,n as d,F as c,e as r,t as i,a as h,h as I,r as m,o as n}from"./_plugin-vue_export-helper.2d9794a3.js";import"./default-i18n.ab92175e.js";import"./_commonjsHelpers.f84db168.js";import"./Tooltip.ae0bcccb.js";import"./index.fd0fcee8.js";import"./Slide.cd756e61.js";const w=""+window.__aioseoDynamicImportPreload__("images/em-graph-preview.4277e799.png"),b=""+window.__aioseoDynamicImportPreload__("images/mi-logo.efba5578.png"),z=""+window.__aioseoDynamicImportPreload__("images/mi-graph-preview.332630b7.png");const S={components:{CoreCard:L,SvgAioseoLogoGear:E,SvgCircleCheck:k,SvgExternal:P},data(){return{emLogoImg:A,emGraphImg:w,miLogoImg:b,miGraphImg:z,installingPlugin:!1,justInstalled:!1,strings:{miLink:this.$t.sprintf("<strong>%1$s</strong>",this.$t.__("Click here",this.$td)),installMi:this.$t.sprintf(this.$t.__("Install %1$s",this.$td),"MonsterInsights"),activateMi:this.$t.sprintf(this.$t.__("Activate %1$s",this.$td),"MonsterInsights"),activateEm:this.$t.sprintf(this.$t.__("Activate %1$s",this.$td),"ExactMetrics"),miInstalled:this.$t.sprintf(this.$t.__("%1$s is Installed & Active",this.$td),"MonsterInsights"),emInstalled:this.$t.sprintf(this.$t.__("%1$s is Installed & Active",this.$td),"ExactMetrics"),setupGA:this.$t.__("Launch Setup Wizard",this.$td),emIntroH:this.$t.__("The Best Google Analytics Plugin for WordPress",this.$td),emIntroP:this.$t.sprintf(this.$t.__("%1$s connects AIOSEO to Google Analytics, providing a powerful integration. %2$s is a sister company of AIOSEO.",this.$td),"ExactMetrics","ExactMetrics"),emIntroLi1:this.$t.__("Quick & Easy Google Analytics Setup",this.$td),emIntroLi2:this.$t.__("Google Analytics Dashboard + Real Time Stats",this.$td),emIntroLi3:this.$t.__("Google Analytics Enhanced Ecommerce Tracking",this.$td),emInstallH:this.$t.sprintf(this.$t.__("Activate %1$s",this.$td),"ExactMetrics"),emInstallP:this.$t.sprintf(this.$t.__("%1$s shows you exactly which content gets the most visits, so you can analyze and optimize it for higher conversions.",this.$td),"ExactMetrics"),miIntroH:this.$t.__("The Best Google Analytics Plugin for WordPress",this.$td),miIntroP:this.$t.sprintf(this.$t.__("%1$s connects AIOSEO to Google Analytics, providing a powerful integration. %2$s is a sister company of AIOSEO.",this.$td),"MonsterInsights","MonsterInsights"),miIntroLi1:this.$t.__("Quick & Easy Google Analytics Setup",this.$td),miIntroLi2:this.$t.__("Google Analytics Dashboard + Real Time Stats",this.$td),miIntroLi3:this.$t.__("Google Analytics Enhanced Ecommerce Tracking",this.$td),miIntroLi4:this.$t.__("Universal Tracking for AMP and Instant Articles",this.$td),miemInstallH:this.$t.__("Install &",this.$td),miInstallH:this.$t.sprintf(this.$t.__("Activate %1$s",this.$td),"MonsterInsights"),miInstallP:this.$t.sprintf(this.$t.__("%1$s shows you exactly which content gets the most visits, so you can analyze and optimize it for higher conversions.",this.$td),"MonsterInsights"),emWizardH:this.$t.sprintf(this.$t.__("Setup %1$s",this.$td),"ExactMetrics"),miWizardH:this.$t.sprintf(this.$t.__("Setup %1$s",this.$td),"MonsterInsights"),emWizardP:this.$t.sprintf(this.$t.__("%1$s has an intuitive setup wizard to guide you through the setup process.",this.$td),"ExactMetrics"),miWizardP:this.$t.sprintf(this.$t.__("%1$s has an intuitive setup wizard to guide you through the setup process.",this.$td),"MonsterInsights")}}},computed:{...y(["options","internalOptions"]),gaActivated(){return this.$aioseo.plugins.miLite.activated||this.$aioseo.plugins.emLite.activated||this.$aioseo.plugins.miPro.activated||this.$aioseo.plugins.emPro.activated},gaInstalled(){return this.$aioseo.plugins.miLite.installed||this.$aioseo.plugins.emLite.installed||this.$aioseo.plugins.miPro.installed||this.$aioseo.plugins.emPro.installed},miOnboardingUrl(){return this.prefersEm?`${this.$aioseo.urls.home}/wp-admin/admin.php?page=exactmetrics-onboarding`:`${this.$aioseo.urls.home}/wp-admin/admin.php?page=monsterinsights-onboarding`},prefersEm(){return(this.$aioseo.plugins.emLite.installed||this.$aioseo.plugins.emPro.installed)&&!this.$aioseo.plugins.miLite.installed&&!this.$aioseo.plugins.miPro.installed}},methods:{...$(["installPlugins"]),installMi(){this.installingPlugin=!0,this.installPlugins([{plugin:this.prefersEm?"emLite":"miLite",type:"plugin"}]).then(()=>{this.installingPlugin=!1,this.justInstalled=!0,this.$aioseo.plugins.miLite.activated=!0,window.aioseo.plugins.miLite.activated=!0}).catch(a=>{console.error(a)})}}},x={class:"aioseo-analytics"},G={id:"aioseo-analytics",class:"aioseo-wrap aioseo-plugin-landing"},H={class:"aioseo-analytics__intro"},C={class:"intro-image"},j=s("span",null,"♥",-1),O=["src"],W={class:"intro-heading"},T={class:"preview-list"},U=["src"],B={class:"intro-image"},D=s("span",null,"♥",-1),N=["src"],V={class:"intro-heading"},R={class:"preview-list"},F=["src"],Q=s("div",{class:"step-count"},[s("span",{class:"step-count__num"},"1")],-1),q={class:"content"},J={class:"step-title"},K={key:0},X={key:1},Y={key:2},Z={key:3},tt={key:4},st=s("div",{class:"step-count"},[s("span",{class:"step-count__num"},"2")],-1),it={class:"content"},et={class:"step-title"},nt={class:"step-body"},ot={class:"step-title"},rt={class:"step-body"};function lt(a,at,ct,ht,t,e){const p=m("svg-aioseo-logo-gear"),g=m("svg-circle-check"),f=m("core-card"),v=m("svg-external"),u=m("base-button");return n(),o("div",x,[s("div",G,[l(f,{slug:"monsterinsights-intro",hideHeader:!0,noSlide:!0,cardClass:{"aioseo-card--intro":!0}},{default:_(()=>[s("div",H,[e.prefersEm?(n(),o(c,{key:0},[s("div",C,[l(p),j,s("img",{src:a.$getAssetUrl(t.emLogoImg),height:"90",alt:"ExactMetrics"},null,8,O)]),s("h2",W,i(t.strings.emIntroH),1),s("p",null,i(t.strings.emIntroP),1),s("div",T,[s("img",{src:a.$getAssetUrl(t.emGraphImg),height:"200",alt:"mi-graph-preview"},null,8,U),s("ul",null,[s("li",null,[l(g),r(" "+i(t.strings.emIntroLi1),1)]),s("li",null,[l(g),r(" "+i(t.strings.emIntroLi2),1)]),s("li",null,[l(g),r(" "+i(t.strings.emIntroLi3),1)])])])],64)):(n(),o(c,{key:1},[s("div",B,[l(p),D,s("img",{src:a.$getAssetUrl(t.miLogoImg),height:"90",alt:"MonsterInsights"},null,8,N)]),s("h2",V,i(t.strings.miIntroH),1),s("p",null,i(t.strings.miIntroP),1),s("div",R,[s("img",{src:a.$getAssetUrl(t.miGraphImg),height:"200",alt:"mi-graph-preview"},null,8,F),s("ul",null,[s("li",null,[l(g),r(" "+i(t.strings.miIntroLi1),1)]),s("li",null,[l(g),r(" "+i(t.strings.miIntroLi2),1)]),s("li",null,[l(g),r(" "+i(t.strings.miIntroLi3),1)]),s("li",null,[l(g),r(" "+i(t.strings.miIntroLi4),1)])])])],64))])]),_:1}),s("section",{class:d(t.justInstalled||e.gaActivated?"aioseo-card step step--completed":"aioseo-card step step--current")},[Q,s("div",q,[s("h2",J,[e.gaInstalled?h("",!0):(n(),o(c,{key:0},[r(i(t.strings.miemInstallH),1)],64)),e.prefersEm?(n(),o(c,{key:1},[r(i(t.strings.emInstallH),1)],64)):(n(),o(c,{key:2},[r(i(t.strings.miInstallH),1)],64))]),s("p",null,[e.prefersEm?(n(),o(c,{key:0},[r(i(t.strings.emInstallP),1)],64)):(n(),o(c,{key:1},[r(i(t.strings.miInstallP),1)],64))]),a.$aioseo.plugins.miLite.canInstall?h("",!0):(n(),I(u,{key:0,type:"blue",size:"medium",tag:"a",target:"_blank",href:a.$aioseo.plugins.miLite.wpLink},{default:_(()=>[l(v),r(" "+i(t.strings.installMi),1)]),_:1},8,["href"])),a.$aioseo.plugins.miLite.canInstall?(n(),I(u,{key:1,loading:t.installingPlugin,type:t.justInstalled||e.gaActivated?"disabled":"blue",size:"medium",onClick:e.installMi},{default:_(()=>[!t.justInstalled&&!e.gaInstalled?(n(),o("span",K,i(t.strings.installMi),1)):h("",!0),(t.justInstalled||e.gaActivated)&&!e.prefersEm?(n(),o("span",X,i(t.strings.miInstalled),1)):h("",!0),(t.justInstalled||e.gaActivated)&&e.prefersEm?(n(),o("span",Y,i(t.strings.emInstalled),1)):h("",!0),!t.justInstalled&&e.gaInstalled&&!e.gaActivated&&!e.prefersEm?(n(),o("span",Z,i(t.strings.activateMi),1)):h("",!0),!t.justInstalled&&e.gaInstalled&&!e.gaActivated&&e.prefersEm?(n(),o("span",tt,i(t.strings.activateEm),1)):h("",!0)]),_:1},8,["loading","type","onClick"])):h("",!0)])],2),s("section",{class:d(t.justInstalled||e.gaActivated?"aioseo-card step step--current":"aioseo-card step step--pending")},[st,s("div",it,[e.prefersEm?(n(),o(c,{key:0},[s("h2",et,i(t.strings.emWizardH),1),s("p",nt,i(t.strings.emWizardP),1)],64)):(n(),o(c,{key:1},[s("h2",ot,i(t.strings.miWizardH),1),s("p",rt,i(t.strings.miWizardP),1)],64)),l(u,{disabled:!t.justInstalled&&!e.gaActivated,type:"blue",size:"medium",tag:"a",href:e.miOnboardingUrl},{default:_(()=>[r(i(t.strings.setupGA),1)]),_:1},8,["disabled","href"])])],2)])])}const Lt=M(S,[["render",lt]]);export{Lt as default};