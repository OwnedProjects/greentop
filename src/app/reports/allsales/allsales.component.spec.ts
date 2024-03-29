import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AllsalesComponent } from './allsales.component';

describe('AllsalesComponent', () => {
  let component: AllsalesComponent;
  let fixture: ComponentFixture<AllsalesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AllsalesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AllsalesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
