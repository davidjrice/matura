<?php namespace Matura\Test\SelfHosted;

use Matura\Test\Support\User;
use Matura\Test\Support\Group;

/**
 * Tests the construction of our test graph - which relies on a lot of code thats
 * too clever for it's own good.
 */
describe('Matura', function ($ctx) {
    describe('Suite', function ($ctx) {
        before(function ($ctx) {
            // Officially, nesting suites in this manner is unsupported.
            $ctx->suite = suite('Suite', function() {
                describe('Fixture', function ($ctx) {
                    it('Skipped', function ($test) {
                        // If you need to skip the test partway into execution.
                        skip();
                    });
                });
            });
            $ctx->describe = $ctx->suite->find('Suite:Fixture');
            $ctx->test = $ctx->suite->find('Suite:Fixture:Skipped');
        });

        it('should be a Suite Block', function ($ctx) {
            expect($ctx->suite)->to->be->an('Matura\Blocks\Suite');
        });

        it('should have a name', function ($ctx) {
            expect($ctx->suite->name())->to->eql('Suite');
        });

        it('should have a path', function ($ctx) {
            expect($ctx->suite->path())->to->eql('Suite');
        });

        it('should not have a parent Suite block', function ($ctx) {
            expect($ctx->suite->parentBlock())->to->eql(null);
        });

        describe('Describe', function($ctx) {
            it('should be a Describe Block', function($ctx) {
                expect($ctx->describe)->to->be->a('Matura\Blocks\Describe');
            });

            it('should have the correct parent Block', function($ctx) {
                expect($ctx->describe->parentBlock())->to->be($ctx->suite);
            });

            describe('TestMethod', function($ctx) {
                it('should be a TestMethod', function($ctx) {
                    expect($ctx->test)->to->be->a('Matura\Blocks\Methods\TestMethod');
                });

                it('should have the correct parent Block', function($ctx) {
                    expect($ctx->test->parentBlock())->to->be($ctx->describe);
                });
            });
        });
    });

    describe('Context', function($ctx) {
        before(function($ctx) {
            $ctx->before_scalar = 5;
            $ctx->user = new User('bob');
        });

        onceBefore(function($ctx) {
            $ctx->once_before_scalar = 10;
            $ctx->group = new Group('admins');
        });

        it('should return null for an undefined value', function($ctx) {
            expect($ctx->never_set)->to->be(null);
        });

        it('should have a user', function($ctx) {
            expect($ctx->user)->to->be->a('\Matura\Test\Support\User');
            expect($ctx->user->name)->to->eql('bob');
        });

        it('should have a group', function($ctx) {
            expect($ctx->group)->to->be->a('\Matura\Test\Support\Group');
            expect($ctx->group->name)->to->eql('admins');
        });

        it('should have a scalar from the before hook', function($ctx) {
            expect($ctx->before_scalar)->to->be(5);
        });

        it('should have a scalar from the once before hook', function($ctx) {
            expect($ctx->once_before_scalar)->to->be(10);
        });

        describe('Nested, Undefined Values', function($ctx) {
            it('should return null for an undefined value when nested deeper', function($ctx) {
                expect($ctx->another_never_set)->to->be(null);
            });
        });

        describe('Sibling-Of Isolation Block', function($ctx) {
            onceBefore(function($ctx) {
                $ctx->once_before_scalar = 15;
            });

            before(function($ctx) {
                $ctx->before_scalar = 10;
                $ctx->group = new Group('staff');
            });

            it("should have the clobbered value of `once_before_scalar`", function($ctx) {
                expect($ctx->once_before_scalar)->to->be(15);
            });

            it("should have the clobbered value of `group`", function($ctx) {
                expect($ctx->group->name)->to->be('staff');
            });
        });

        describe('Isolation', function() {
          it("should have the parent `once_before_scalar` and not a sibling's", function($ctx) {
              expect($ctx->once_before_scalar)->to->be(10);
          });

          it("should have the parent `before_scalar` and not a sibling's", function($ctx) {
              expect($ctx->before_scalar)->to->be(5);
          });
        });
    });
});