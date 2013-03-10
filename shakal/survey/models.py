# -*- coding: utf-8 -*-
from datetime import datetime

from django.conf import settings
from django.contrib.contenttypes import generic
from django.contrib.contenttypes.models import ContentType
from django.core.exceptions import ValidationError
from django.db import models
from django.db.models import permalink
from django.utils.translation import ugettext_lazy as _


class SurveyListManager(models.Manager):
	def get_query_set(self):
		return super(SurveyListManager, self).get_query_set().filter(approved = True, active_from__lte = datetime.now(), content_type_id = None).order_by("-active_from")


class Survey(models.Model):
	objects = models.Manager()
	surveys = SurveyListManager()

	question = models.TextField(verbose_name = _("question"))
	slug = models.SlugField(unique = True)
	checkbox = models.BooleanField(default = False, verbose_name = _("more answers"))
	approved = models.BooleanField(default = False, verbose_name = _("approved"))
	active_from = models.DateTimeField(blank = True, null = True, verbose_name = _("active from"))
	answer_count = models.PositiveIntegerField(default = 0)

	content_type = models.ForeignKey(ContentType, limit_choices_to = {"model__in": ("article",)}, null = True, blank = True, verbose_name = _("content type"))
	object_id = models.PositiveIntegerField(null = True, blank = True, verbose_name = _("object id"))
	content_object = generic.GenericForeignKey('content_type', 'object_id')

	@property
	def answers(self):
		return Answer.objects.filter(survey = self.pk).select_related('survey__answer_count').order_by('pk')

	@permalink
	def get_absolute_url(self):
		return ('survey:detail-by-slug', None, {'slug': self.slug})

	@permalink
	def get_list_url(self):
		return ('survey:list', None, None)

	def msg_id(self):
		return 'survey-' + str(self.pk)

	def clean(self):
		if self.content_type and not self.object_id:
			raise ValidationError(_('Field object id is required'))
		if self.object_id and not self.content_type:
			raise ValidationError(_('Field content type is required'))
		if self.content_type:
			self.slug = self.content_type.model + '-' + str(self.object_id)

	def __unicode__(self):
		return self.question

	class Meta:
		verbose_name = _('survey')
		verbose_name_plural = _('surveys')


class Answer(models.Model):
	survey = models.ForeignKey(Survey)
	answer = models.CharField(max_length = 255)
	votes = models.PositiveIntegerField(default = 0)

	def percent(self):
		if self.survey.answer_count == 0:
			return 0
		else:
			return 100.0 * self.votes / self.survey.answer_count

	def __unicode__(self):
		return self.answer

	class Meta:
		verbose_name = _('answer')
		verbose_name_plural = _('answers')


class RecordIp(models.Model):
	survey = models.ForeignKey(Survey)
	ip = models.GenericIPAddressField()
	date = models.DateTimeField(auto_now_add = True)

	class Meta:
		unique_together = ('survey', 'ip', )


class RecordUser(models.Model):
	survey = models.ForeignKey(Survey)
	user = models.ForeignKey(settings.AUTH_USER_MODEL)
	date = models.DateTimeField(auto_now_add = True)

	class Meta:
		unique_together = ('survey', 'user', )


def check_can_vote(request, survey):
	if request.user.is_authenticated():
		return not RecordUser.objects.filter(user = request.user, survey = survey).exists()
	else:
		return not RecordIp.objects.filter(ip = request.META['REMOTE_ADDR'], survey = survey).exists()


def record_vote(request, survey):
	if request.user.is_authenticated():
		RecordUser(user = request.user, survey = survey).save()
	else:
		RecordIp(ip = request.META['REMOTE_ADDR'], survey = survey).save()
