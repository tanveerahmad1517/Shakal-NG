# -*- coding: utf-8 -*-

from django.conf.urls.defaults import patterns, url
import shakal.linuxos.redirect_views as redirect_views

urlpatterns = patterns('',
	url('^profil/(?P<pk>[0-9]+)/index.html$', redirect_views.profile_redirect),
)
