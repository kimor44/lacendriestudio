import{d as i,a as o,b as l,m as c}from"./vuex.esm-bundler.8589b2dd.js";import{a as g}from"./helpers.de7566d0.js";import{R as p}from"./RequiresUpdate.52f5acf2.js";import{h as m}from"./postContent.bb42e0a8.js";import{C as f}from"./Index.1fd8fc42.js";import{S as _}from"./Caret.42a820e0.js";const T={computed:{...i(["isUnlicensed"]),shouldShowMain(){return!this.isUnlicensed&&this.$addons.isActive(this.addonSlug)&&!this.$addons.requiresUpgrade(this.addonSlug)&&this.$addons.hasMinimumVersion(this.addonSlug)},shouldShowActivate(){return!this.isUnlicensed&&!this.$addons.isActive(this.addonSlug)&&this.$addons.canActivate(this.addonSlug)&&!this.$addons.requiresUpgrade(this.addonSlug)&&(this.$addons.hasMinimumVersion(this.addonSlug)||!this.$addons.isInstalled(this.addonSlug))},shouldShowUpdate(){return!this.isUnlicensed&&this.$addons.isInstalled(this.addonSlug)&&!this.$addons.requiresUpgrade(this.addonSlug)&&!this.$addons.hasMinimumVersion(this.addonSlug)},shouldShowLite(){return this.isUnlicensed||this.$addons.requiresUpgrade(this.addonSlug)}}},A={computed:{...i(["isUnlicensed"]),shouldShowLite(){return this.isUnlicensed}},methods:{shouldShowMain(s,e){return!this.isUnlicensed&&this.$license.hasCoreFeature(this.$aioseo,s,e)},shouldShowUpgrade(s,e){return!this.isUnlicensed&&!this.$license.hasCoreFeature(this.$aioseo,s,e)}}},N={computed:{...o(["networkData"]),getSites(){return JSON.parse(JSON.stringify(this.networkData.sites.sites))},getSitesIds(){return Array.from(this.getSites,s=>this.getUniqueSiteId(s))},inactiveSitesIds(){return g(this.getSitesIds,this.activeSitesIds)},activeSitesIds(){const s=[];return this.getSites.forEach(e=>{this.networkData.activeSites.some(t=>t.domain===e.domain&&t.path===e.path)&&s.push(this.getUniqueSiteId(e))}),s}},methods:{getUniqueSiteId(s){return`${s.blog_id}_${s.domain}_${s.path}`},getMainSite(){let s=null;return this.getSites.forEach(e=>{this.isMainSite(e.domain,e.path)&&(s=e)}),s},isMainSite(s,e){return(this.$aioseo.urls.mainSiteUrl+"/").includes(`${this.$aioseo.data.isSsl?"https":"http"}://${s}${e}`)},getSiteByDomainAndPath(s,e){return this.getSites.find(t=>t.domain===s&&t.path===e)},getSiteByUniqueId(s){return this.getSites.find(e=>this.getUniqueSiteId(e)===s)},getActiveSiteByUniqueId(s){const e=this.getSiteByUniqueId(s);return e?this.networkData.activeSites.find(t=>t.domain===e.domain&&t.path===e.path):null},isSiteActive(s){return this.activeSitesIds.includes(this.getUniqueSiteId(s))},parseSiteValue(s){const e=[];return s.forEach(t=>{e.push(this.getSiteByUniqueId(t))}),e}}},I={data(){return{strings:{notifications:this.$t.__("Notifications",this.$td),newNotifications:this.$t.__("New Notifications",this.$td),activeNotifications:this.$t.__("Active Notifications",this.$td)}}},computed:{...i(["activeNotifications","activeNotificationsCount","dismissedNotifications","dismissedNotificationsCount"]),notificationsCount(){return this.dismissed?this.dismissedNotificationsCount:this.activeNotificationsCount},notifications(){return this.dismissed?this.dismissedNotifications:this.activeNotifications},notificationTitle(){return this.dismissed?this.strings.notifications:this.strings.newNotifications}},methods:{...l(["toggleDismissedNotifications","toggleNotifications"])}},L={computed:{...i(["isUnlicensed"])},methods:{getExcludedUpdateTabs(s){if(!this.isUnlicensed&&this.$addons.hasMinimumVersion(s)&&!this.$addons.requiresUpgrade(s))return[];const e=[];return this.$router.options.routes.forEach(t=>{if(!t.meta||!t.meta.middleware)return;(Array.isArray(t.meta.middleware)?t.meta.middleware:[t.meta.middleware]).some(n=>n===p)&&e.push(t.name)}),e}}},P={computed:{...o(["currentPost","tags"]),...o("live-tags",["liveTags"])},methods:{parseTags(s){return!s||!this.tags.tags?s:(this.tags.tags.forEach(e=>{if(e.id==="custom_field"){const a=new RegExp(`#${e.id}-([a-zA-Z0-9_-]+)`),r=s.match(a);r&&r[1]&&(s=s.replace(a,m(r[1])));return}if(e.id==="tax_name"){const a=new RegExp(`#${e.id}-([a-zA-Z0-9_-]+)`,"g");s=s.replace(a,`[${e.name} - $1]`);return}const t=new RegExp(`#${e.id}(?![a-zA-Z0-9_])`,"g");e.id==="separator_sa"&&this.separator!==void 0&&(s=s.replace(t,this.separator));const d=s.match(t),n=this.liveTags[e.id]||e.value;d&&(s=s.replace(t,"%|%"+n)),e.value=n;const{tags:u}=window.aioseo.tags,h=u.find(a=>a.id===e.id);h&&(h.value=n)}),s=s.replace(/%\|%/g,""),this.$tags.decodeHTMLEntities(this.$tags.decodeHTMLEntities(s.replace(/<(?:.|\n)*?>/gm," ").replace(/\s/g," "))))}}},k={computed:{...i(["isUnlicensed"]),toolsSettings(){const s=[{value:"webmasterTools",label:this.$t.__("Webmaster Tools",this.$td),access:"aioseo_general_settings"},{value:"rssContent",label:this.$t.__("RSS Content",this.$td),access:"aioseo_general_settings"},{value:"advanced",label:this.$t.__("Advanced",this.$td),access:"aioseo_general_settings"},{value:"searchAppearance",label:this.$t.__("Search Appearance",this.$td),access:"aioseo_search_appearance_settings"},{value:"social",label:this.$t.__("Social Networks",this.$td),access:"aioseo_social_networks_settings"},{value:"sitemap",label:this.$t.__("Sitemaps",this.$td),access:"aioseo_sitemap_settings"},{value:"robots",label:this.$t.__("Robots.txt",this.$td),access:"aioseo_tools_settings"},{value:"breadcrumbs",label:this.$t.__("Breadcrumbs",this.$td),access:"aioseo_general_settings"}];return window.aioseo.internalOptions.internal.deprecatedOptions.includes("badBotBlocker")&&s.push({value:"blocker",label:this.$t.__("Bad Bot Blocker",this.$td),access:"aioseo_tools_settings"}),this.$isPro&&s.push({value:"accessControl",label:this.$t.__("Access Control",this.$td),access:"aioseo_admin"}),!this.isUnlicensed&&this.showImageSeoReset&&s.push({value:"image",label:this.$t.__("Image SEO",this.$td),access:"aioseo_search_appearance_settings"}),!this.isUnlicensed&&this.showLocalBusinessReset&&s.push({value:"localBusiness",label:this.$t.__("Local Business SEO",this.$td),access:"aioseo_local_seo_settings"}),!this.isUnlicensed&&this.showRedirectsReset&&s.push({value:"redirects",label:this.$t.__("Redirects",this.$td),access:"aioseo_redirects_settings"}),!this.isUnlicensed&&this.showLinkAssistantReset&&s.push({value:"linkAssistant",label:this.$t.__("Link Assistant",this.$td),access:"aioseo_link_assistant_settings"}),s.filter(e=>this.$allowed(e.access))},showImageSeoReset(){const s=this.$addons.getAddon("aioseo-image-seo");return s&&s.isActive&&!s.requiresUpgrade},showLocalBusinessReset(){const s=this.$addons.getAddon("aioseo-local-business");return s&&s.isActive&&!s.requiresUpgrade},showRedirectsReset(){const s=this.$addons.getAddon("aioseo-redirects");return s&&s.isActive&&!s.requiresUpgrade},showLinkAssistantReset(){const s=this.$addons.getAddon("aioseo-link-assistant");return s&&s.isActive&&!s.requiresUpgrade}}},R={data(){return{strings:{skipThisStep:this.$t.__("Skip this Step",this.$td),goBack:this.$t.__("Go Back",this.$td),saveAndContinue:this.$t.__("Save and Continue",this.$td)}}},computed:{...i("wizard",["getNextLink","getPrevLink"]),...i(["isUnlicensed"]),features(){return[...this.$constants.WIZARD_FEATURES]},getSelectedUpsellFeatures(){return this.presetFeatures?this.presetFeatures.filter(s=>this.needsUpsell(this.features.find(e=>e.value===s))).map(s=>this.features.find(e=>e.value===s)):[]}},methods:{...l("wizard",["setCurrentStage"]),needsUpsell(s){return s.pro?!!(this.isUnlicensed||s.upgrade&&this.$addons.requiresUpgrade(s.upgrade)):!1}},mounted(){this.setCurrentStage(this.stage)}},y={components:{CoreModal:f,SvgClose:_},data(){return{loading:!1,showModal:!1,strings:{closeAndExit:this.$t.__("Close and Exit Wizard Without Saving",this.$td),buildABetterAioseo:this.$t.sprintf(this.$t.__("Build a Better %1$s",this.$td),"AIOSEO"),getImprovedFeatures:this.$t.sprintf(this.$t.__("Get improved features and faster fixes by sharing non-sensitive data via usage tracking that shows us how %1$s is being used. No personal data is tracked or stored.",this.$td),"AIOSEO"),noThanks:this.$t.__("No thanks",this.$td),yesCountMeIn:this.$t.__("Yes, count me in!",this.$td)}}},computed:{...o("wizard",["smartRecommendations"])},methods:{...c("wizard",["saveWizard"]),processOptIn(){this.smartRecommendations.usageTracking=!0,this.loading=!0,this.saveWizard("smartRecommendations").then(()=>{window.location.href=this.$aioseo.urls.aio.dashboard})}}},B={data(){return{resultsPerPage:20,orderBy:null,orderDir:"asc",searchTerm:"",pageNumber:1,filter:"all",wpTableKey:0,wpTableLoading:!1}},methods:{...c(["changeItemsPerPage"]),refreshTable(){return this.wpTableLoading=!0,this.processFetchTableData().then(()=>this.wpTableLoading=!1)},processAdditionalFilters({filters:s}){this.wpTableLoading=!0,this.processFetchTableData(s).then(()=>this.wpTableLoading=!1)},processSearch(s){typeof s=="object"&&(s=s.target.value),this.pageNumber=1,this.searchTerm=s,this.wpTableLoading=!0,this.processFetchTableData().then(()=>this.wpTableLoading=!1)},processPagination(s){this.pageNumber=s,this.wpTableLoading=!0,this.processFetchTableData().then(()=>this.wpTableLoading=!1)},processFilterTable(s){this.filter=s.slug,this.searchTerm="",this.pageNumber=1,this.wpTableLoading=!0,this.resetSelectedFilters(),this.processFetchTableData().then(()=>this.wpTableLoading=!1)},processChangeItemsPerPage(s){this.pageNumber=1,this.resultsPerPage=s,this.wpTableLoading=!0,this.changeItemsPerPage({slug:this.changeItemsPerPageSlug,value:s}).then(()=>this.processFetchTableData().then(()=>this.$scrollTo(`#${this.tableId}`,{offset:-110}))).then(()=>this.wpTableLoading=!1)},processSort(s,e){e.target.blur(),this.orderBy=s.slug,this.orderDir=this.orderBy!==s.slug?s.sortDir:s.sortDir==="asc"?"desc":"asc",this.wpTableLoading=!0,this.processFetchTableData().then(()=>this.wpTableLoading=!1)},processFetchTableData(s){return this.fetchData({slug:this.slug||null,orderBy:this.orderBy,orderDir:this.orderDir,limit:this.resultsPerPage,offset:this.pageNumber===1?0:(this.pageNumber-1)*this.resultsPerPage,searchTerm:this.searchTerm,filter:this.filter,additionalFilters:s||this.selectedFilters})},resetSelectedFilters(){}},created(){this.resultsPerPage=this.$aioseo.settings.tablePagination[this.changeItemsPerPageSlug]||this.resultsPerPage}};export{T as A,A as L,I as N,L as R,P as T,B as W,R as a,y as b,N as c,k as d};